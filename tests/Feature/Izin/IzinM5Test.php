<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Notifikasi;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\User;
use App\Services\Izin\PengajuanIzinService;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class IzinM5Test extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $dosenPa;
    protected User $admin;
    protected JenisIzin $jenisIzin;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 1, 'name' => 'Dosen PA User']);
        $this->admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create(['kelas' => 'TP-1A', 'nama_kelas' => 'TP-1A', 'prodi_id' => $prodi->id, 'dosen_pa_id' => $this->dosenPa->id, 'level_kelas_id' => $levelKelas->id]);
        $blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
        ]);

        $this->jenisIzin = JenisIzin::create([
            'kode' => 'IZIN_M5_' . rand(1000, 9999),
            'nama' => 'Izin M5 Test',
            'butuh_ukm' => false,
            'min_ajukan_jam' => 0,
            'maks_durasi_jam' => 24,
            'is_active' => true,
        ]);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisIzin->id,
            'urutan' => 1,
            'label' => 'Persetujuan Dosen PA',
            'resolver' => 'dosen_pa',
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);
    }

    public function test_konfirmasi_tiba_publik_signed_url_berhasil_dan_sekali_pakai(): void
    {
        $service = app(PengajuanIzinService::class);
        $pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Lomba Surabaya',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);

        $approval = $pengajuan->approvals->first();
        $service->setujui($approval, $this->dosenPa);
        $pengajuan->refresh();

        // 1. Akses Halaman Form Konfirmasi Tiba
        $signedUrlShow = URL::signedRoute('publik.konfirmasi.tiba.show', ['qr_token' => $pengajuan->qr_token]);
        $responseShow = $this->get($signedUrlShow);
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Formulir Kedatangan Mahasiswa');

        // 2. Submit Konfirmasi Tiba
        $signedUrlStore = URL::signedRoute('publik.konfirmasi.tiba.store', ['qr_token' => $pengajuan->qr_token]);
        $responseStore = $this->post($signedUrlStore, [
            'tiba_dikonfirmasi_oleh' => 'Pak RT Supriyadi',
            'tiba_di' => 'Surabaya',
            'tiba_kontak' => '081299998888',
        ]);

        $responseStore->assertRedirect($signedUrlShow);
        $pengajuan->refresh();
        $this->assertNotNull($pengajuan->tiba_at);
        $this->assertEquals('Pak RT Supriyadi', $pengajuan->tiba_dikonfirmasi_oleh);

        // 3. Akses Ulang (Sekali pakai -> Menampilkan tanda terima statis)
        $responseRevisit = $this->get($signedUrlShow);
        $responseRevisit->assertStatus(200);
        $responseRevisit->assertSee('Tanda Terima Konfirmasi Kedatangan');
        $responseRevisit->assertDontSee('Formulir Kedatangan Mahasiswa');
    }

    public function test_monitor_asrama_dapat_diakses_staf_dan_mengembalikan_data_json(): void
    {
        // 1. Access Index Page
        $responseIndex = $this->actingAs($this->admin)->get(route('admin.izin.monitor'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Monitor Asrama');

        // 2. Access JSON Data Endpoint for 60s Auto-Refresh
        $responseData = $this->actingAs($this->admin)->get(route('admin.izin.monitor.data'));
        $responseData->assertStatus(200);
        $responseData->assertJsonStructure([
            'success',
            'stats' => ['sedang_berjalan', 'terlambat', 'mendatang'],
            'data',
            'last_updated',
        ]);
    }

    public function test_notifikasi_in_app_terkirim_dan_menghitung_badge_unread(): void
    {
        $notifService = app(NotifikasiService::class);
        $notifService->kirim($this->admin->id, 'Judul Test', 'Pesan Test Notification');

        $this->assertEquals(1, $notifService->countUnread($this->admin->id));
        $this->assertDatabaseHas('notifikasis', [
            'user_id' => $this->admin->id,
            'judul' => 'Judul Test',
            'is_read' => false,
        ]);
    }

    /**
     * TEST UTAMA IDEMPOTENSI: Job/Command dijalankan 2 kali berturut-turut TIDAK menghasilkan efek ganda.
     */
    public function test_idempotensi_job_terjadwal_dijalankan_dua_kali_tanpa_efek_ganda(): void
    {
        $now = Carbon::now();

        // 1. Idempotensi TandaiIzinKadaluarsaCommand
        $pengajuanBasi = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Izin Hang',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => $now->copy()->subHours(4), // >2 jam terlewat
            'waktu_kembali' => $now->copy()->addHours(2),
            'nama_snapshot' => $this->student->name,
            'status' => 'diajukan',
        ]);

        Artisan::call('izin:tandai-kadaluarsa');
        $this->assertEquals('kadaluarsa', $pengajuanBasi->fresh()->status);

        // Run second time (Idempotent check)
        Artisan::call('izin:tandai-kadaluarsa');
        $this->assertEquals('kadaluarsa', $pengajuanBasi->fresh()->status);

        // 2. Idempotensi PeriksaKeterlambatanCommand
        $pengajuanTerlambat = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Belanja Terlambat',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => $now->copy()->subHours(5),
            'waktu_kembali' => $now->copy()->subHour(1), // Terlewat 1 jam
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
        ]);

        Artisan::call('izin:periksa-keterlambatan');
        $this->assertEquals('terlambat', $pengajuanTerlambat->fresh()->status);
        $this->assertEquals(1, Pelanggaran::where('user_id', $this->student->id)->count());

        // Run second time (Idempotent check: DOES NOT CREATE DUPLICATE VIOLATIONS)
        Artisan::call('izin:periksa-keterlambatan');
        $this->assertEquals(1, Pelanggaran::where('user_id', $this->student->id)->count());

        // 3. Idempotensi IngatkanApproverCommand
        $pengajuanApprover = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Tunggu Approver',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => $now->copy()->addHours(5),
            'waktu_kembali' => $now->copy()->addHours(10),
            'nama_snapshot' => $this->student->name,
            'status' => 'diajukan',
        ]);

        IzinApproval::create([
            'pengajuan_izin_id' => $pengajuanApprover->id,
            'urutan' => 1,
            'label_snapshot' => 'Persetujuan Dosen PA',
            'approver_user_id' => $this->dosenPa->id,
            'status' => 'menunggu',
            'dibuka_at' => $now->copy()->subHours(15), // >12 jam
        ]);

        Artisan::call('izin:ingatkan-approver');
        $this->assertEquals(1, Notifikasi::where('user_id', $this->dosenPa->id)->count());

        // Run second time (Idempotent check: DOES NOT CREATE DUPLICATE NOTIFICATIONS)
        Artisan::call('izin:ingatkan-approver');
        $this->assertEquals(1, Notifikasi::where('user_id', $this->dosenPa->id)->count());
    }
}
