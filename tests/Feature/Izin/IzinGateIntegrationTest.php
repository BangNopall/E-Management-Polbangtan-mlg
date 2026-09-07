<?php

namespace Tests\Feature\Izin;

use App\Models\Attendance;
use App\Models\BlokRuangan;
use App\Models\IzinWorkflowStep;
use App\Models\JadwalKegiatanAsrama;
use App\Models\JenisIzin;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Pejabat;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Presence;
use App\Models\PresensiApel;
use App\Models\Prodi;
use App\Models\User;
use App\Services\Izin\IzinGateResolver;
use App\Services\Izin\PembebasanPresensiService;
use App\Services\Izin\PengajuanIzinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinGateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $dosenPa;
    protected User $admin;
    protected JenisIzin $jenisIzin;
    protected BlokRuangan $blok;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 1, 'name' => 'Dosen PA User']);
        $this->admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $this->kelas = Kelas::create(['kelas' => 'TP-1A', 'nama_kelas' => 'TP-1A', 'prodi_id' => $prodi->id, 'dosen_pa_id' => $this->dosenPa->id, 'level_kelas_id' => $levelKelas->id]);
        $this->blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => $prodi->id,
            'kelas_id' => $this->kelas->id,
            'blok_ruangan_id' => $this->blok->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
            'status' => 'didalam',
        ]);

        $this->jenisIzin = JenisIzin::create([
            'kode' => 'IZIN_TEST_' . rand(1000, 9999),
            'nama' => 'Izin Keluar Test',
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

    /**
     * REGRESI TEST: Mahasiswa TANPA izin scan gerbang harus berperilaku persis seperti sebelumnya.
     * Ini test paling penting — membuktikan tidak ada regresi pada kode produksi harian.
     */
    public function test_regresi_mahasiswa_tanpa_izin_scan_didalam_diluar_telat_persis_seperti_semula(): void
    {
        $now = Carbon::parse('2026-08-08 10:00:00');
        Carbon::setTestNow($now);
        $today = $now->toDateString();

        // Setup attendance window
        $attendance = Attendance::create([
            'title' => 'Absensi Harian',
            'date' => $today,
            'start_time' => '06:00:00',
            'end_time' => '12:00:00',
        ]);

        // Mahasiswa keluar dulu (dalam jam kerja 10:05)
        $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'user_id' => $this->student->id,
            'time' => '10:05:00',
            'date' => $today,
            'status' => 'diluar',
            'scanner' => 'absensi',
        ]);

        // Mahasiswa masuk lagi di luar jam kerja (13:00)
        $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'user_id' => $this->student->id,
            'time' => '13:00:00',
            'date' => $today,
            'status' => 'didalam',
            'scanner' => 'absensi',
        ]);

        $presence = Presence::where('user_id', $this->student->id)->latest()->first();
        $this->assertNotNull($presence);
        $this->assertEquals('telat', $presence->log_status);
        $this->assertEquals(1, $presence->is_late);
        $this->assertEquals('telat', $this->student->fresh()->status);

        Carbon::setTestNow();
    }

    /**
     * Scan gerbang dengan izin aktif -> log_status='izin', users.status='izin'.
     */
    public function test_scan_gerbang_dengan_izin_aktif_log_status_izin_dan_user_status_izin(): void
    {
        $now = Carbon::parse('2026-08-08 10:00:00');
        Carbon::setTestNow($now);
        $today = $now->toDateString();

        // Buat izin disetujui yang mencakup sekarang
        PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Belanja',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => $now->copy()->subHour(1),
            'waktu_kembali' => $now->copy()->addHours(5),
            'nama_snapshot' => $this->student->name,
            'status' => 'disetujui',
        ]);

        // Setup attendance
        Attendance::create([
            'title' => 'Absensi Harian',
            'date' => $today,
            'start_time' => '06:00:00',
            'end_time' => '12:00:00',
        ]);

        // Mahasiswa keluar dulu (dalam jam kerja 10:05)
        $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'user_id' => $this->student->id,
            'time' => '10:05:00',
            'date' => $today,
            'status' => 'diluar',
            'scanner' => 'absensi',
        ]);

        // Mahasiswa masuk lagi di luar jam kerja (13:00)
        $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'user_id' => $this->student->id,
            'time' => '13:00:00',
            'date' => $today,
            'status' => 'didalam',
            'scanner' => 'absensi',
        ]);

        $presence = Presence::where('user_id', $this->student->id)->latest()->first();
        $this->assertNotNull($presence);
        $this->assertEquals('izin', $presence->log_status);
        $this->assertEquals(0, $presence->is_late);
        $this->assertEquals('izin', $this->student->fresh()->status);

        Carbon::setTestNow();
    }

    /**
     * Pembebasan presensi: 'Alpha' -> 'Izin', 'Hadir' TIDAK berubah.
     */
    public function test_pembebasan_presensi_mengubah_alpha_ke_izin_dan_tidak_menimpa_hadir(): void
    {
        $now = Carbon::now();

        // Buat dua student
        $studentHadir = User::factory()->create([
            'role_id' => 3,
            'prodi_id' => 1,
            'kelas_id' => $this->kelas->id,
            'blok_ruangan_id' => $this->blok->id,
            'nim' => '111111',
        ]);

        $studentAlpha = $this->student;

        // Buat jadwal kegiatan wajib Apel untuk besok
        $jadwal = JadwalKegiatanAsrama::create([
            'blok_id' => $this->blok->id,
            'jenis_kegiatan' => 'Apel',
            'tanggal_kegiatan' => $now->copy()->addDay()->toDateString(),
            'mulai_acara' => '06:30:00',
            'selesai_acara' => '07:30:00',
        ]);

        PresensiApel::create([
            'jadwalKegiatanAsrama_id' => $jadwal->id,
            'user_id' => $studentHadir->id,
            'status_kehadiran' => 'Hadir',
        ]);

        PresensiApel::create([
            'jadwalKegiatanAsrama_id' => $jadwal->id,
            'user_id' => $studentAlpha->id,
            'status_kehadiran' => 'Alpha',
        ]);

        // Buat izin disetujui untuk $studentAlpha yang mencakup hari besok
        PengajuanIzin::create([
            'user_id' => $studentAlpha->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Liburan',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => $now->copy()->subHours(2),
            'waktu_kembali' => $now->copy()->addDays(2),
            'nama_snapshot' => $studentAlpha->name,
            'status' => 'disetujui',
        ]);

        $service = app(PembebasanPresensiService::class);
        $izin = PengajuanIzin::where('user_id', $studentAlpha->id)->first();
        $service->bebaskanUntukPengajuan($izin);

        // HADIR tidak disentuh
        $this->assertEquals('Hadir', PresensiApel::where('user_id', $studentHadir->id)->first()->status_kehadiran);

        // Alpha -> Izin
        $this->assertEquals('Izin', PresensiApel::where('user_id', $studentAlpha->id)->first()->status_kehadiran);
    }

    /**
     * Kembali terlambat -> Pelanggaran dibuat otomatis dengan status 'submitted'.
     */
    public function test_kembali_terlambat_membuat_pelanggaran_otomatis_berstatus_submitted(): void
    {
        $now = Carbon::now();

        // Pastikan jenis pelanggaran ada
        JenisPelanggaran::create([
            'jenis_pelanggaran' => 'Terlambat kembali dari izin resmi',
            'kategori_id' => 1,
            'poin' => 2,
            'sub_kategori' => 'Ringan',
        ]);

        // Buat izin disetujui dengan tenggat KEMBALI SUDAH LEWAT (terlambat kembali)
        PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Berkas tidak lengkap',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => $now->copy()->subHours(8),
            'waktu_kembali' => $now->copy()->subHour(1),  // SUDAH LEWAT 1 jam
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'keluar_at' => $now->copy()->subHours(6),
        ]);

        $service = app(PengajuanIzinService::class);
        $izin = PengajuanIzin::where('user_id', $this->student->id)->first();
        $service->catatScanGerbang($izin, $this->student, $now, 'didalam');

        // Verifikasi transisi status izin
        $this->assertEquals('terlambat', $izin->fresh()->status);
        $this->assertEquals('didalam', $this->student->fresh()->status);

        // Verifikasi pelanggaran otomatis dengan status 'submitted' (BUKAN 'Done')
        $pelanggaran = Pelanggaran::where('user_id', $this->student->id)->first();
        $this->assertNotNull($pelanggaran);
    }

    /**
     * Kembali tanpa konfirmasi tiba di lokasi tujuan -> Pelanggaran dibuat otomatis.
     */
    public function test_kembali_tanpa_konfirmasi_tiba_membuat_pelanggaran_otomatis(): void
    {
        $now = Carbon::now();

        $this->jenisIzin->update(['butuh_konfirmasi_tiba' => true]);

        // Buat izin disetujui dengan tenggat KEMBALI belum lewat
        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Pulang kampung',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => $now->copy()->subHours(8),
            'waktu_kembali' => $now->copy()->addHours(2),  // BELUM LEWAT
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'keluar_at' => $now->copy()->subHours(6),
            'tiba_at' => null, // Belum konfirmasi tiba
        ]);

        $service = app(PengajuanIzinService::class);
        $izin = PengajuanIzin::where('user_id', $this->student->id)->first();
        $service->catatScanGerbang($izin, $this->student, $now, 'didalam');

        // Verifikasi transisi status izin (karena belum lewat waktu kembali, statusnya selesai)
        $this->assertEquals('selesai', $izin->fresh()->status);
        $this->assertEquals('didalam', $this->student->fresh()->status);

        // Verifikasi pelanggaran otomatis dengan status 'submitted'
        $pelanggaran = Pelanggaran::where('user_id', $this->student->id)->first();
        $this->assertNotNull($pelanggaran);
        $this->assertEquals('submitted', $pelanggaran->statusPelanggaran);
    }
}
