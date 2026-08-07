<?php

namespace Tests\Feature\Izin;

use App\Models\BlokRuangan;
use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Pejabat;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use App\Models\User;
use App\Services\Izin\PengajuanIzinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinApproverTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected User $dosenPa;
    protected User $kaprodiUser;
    protected User $otherStaff;
    protected JenisIzin $jenisIzin;
    protected PengajuanIzin $pengajuan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Target Data Mahasiswa & Civitas
        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $this->dosenPa = User::factory()->create(['role_id' => 1, 'name' => 'Dosen PA User']);
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

        $this->kaprodiUser = User::factory()->create(['role_id' => 1, 'name' => 'Kaprodi User']);
        Pejabat::create([
            'jabatan' => 'kaprodi',
            'user_id' => $this->kaprodiUser->id,
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        $this->otherStaff = User::factory()->create(['role_id' => 1, 'name' => 'Unrelated Staff']);

        // 2. Setup Jenis Izin 2 Langkah: Langkah 1 = Dosen PA, Langkah 2 = Kaprodi
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

        IzinWorkflowStep::create([
            'jenis_izin_id' => $this->jenisIzin->id,
            'urutan' => 2,
            'label' => 'Persetujuan Kaprodi',
            'resolver' => 'pejabat',
            'jabatan' => ['kaprodi'],
            'mode' => 'any',
            'resolve_saat' => 'submit',
        ]);

        // 3. Submit Pengajuan Izin
        $service = app(PengajuanIzinService::class);
        $this->pengajuan = $service->ajukan($this->student, [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Keperluan mendesak',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now()->addHours(2),
            'waktu_kembali' => now()->addHours(6),
        ]);
    }

    public function test_approver_dapat_melihat_daftar_inbox_persetujuan_miliknya(): void
    {
        // Dosen PA harus melihat 1 pengajuan pending di inbox
        $response = $this->actingAs($this->dosenPa)->get(route('admin.izin.persetujuan.inbox'));
        $response->assertStatus(200);
        $response->assertSee('Keperluan mendesak');

        // Kaprodi belum bisa melihat di inbox karena langkah 2 belum aktif
        $responseKaprodi = $this->actingAs($this->kaprodiUser)->get(route('admin.izin.persetujuan.inbox'));
        $responseKaprodi->assertStatus(200);
        $responseKaprodi->assertDontSee('Keperluan mendesak');
    }

    public function test_approver_bukan_penandatangan_berhak_returns_403(): void
    {
        // Other Staff mencoba me-review / memutuskan langkah milik Dosen PA
        $responseReview = $this->actingAs($this->otherStaff)->get(route('admin.izin.persetujuan.review', $this->pengajuan->id));
        $responseReview->assertStatus(403);

        $responsePutuskan = $this->actingAs($this->otherStaff)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'setujui',
        ]);
        $responsePutuskan->assertStatus(403);
    }

    public function test_langkah_n_tidak_dapat_diputuskan_saat_langkah_sebelumnya_masih_menunggu_returns_409(): void
    {
        // Kaprodi (approver langkah 2) mencoba memutuskan saat pengajuan masih di langkah 1
        $responsePutuskan = $this->actingAs($this->kaprodiUser)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'setujui',
        ]);
        $responsePutuskan->assertStatus(409);
    }

    public function test_penolakan_tanpa_alasan_ditolak_by_server_side_validation(): void
    {
        // Dosen PA menolak tanpa mengisi catatan
        $response = $this->actingAs($this->dosenPa)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'tolak',
            'catatan' => '',
        ]);
        $response->assertSessionHasErrors('catatan');
    }

    public function test_approver_dapat_menyetujui_dan_meneruskan_ke_langkah_berikutnya(): void
    {
        // 1. Dosen PA menyetujui Langkah 1
        $responseStep1 = $this->actingAs($this->dosenPa)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'setujui',
            'catatan' => 'Disetujui Dosen PA',
        ]);
        $responseStep1->assertRedirect(route('admin.izin.persetujuan.inbox'));

        $this->pengajuan->refresh();
        $this->assertEquals(2, $this->pengajuan->langkah_aktif);
        $this->assertEquals('diajukan', $this->pengajuan->status);

        $approvalStep1 = IzinApproval::where('pengajuan_izin_id', $this->pengajuan->id)->where('urutan', 1)->first();
        $this->assertEquals('disetujui', $approvalStep1->status);
        $this->assertEquals($this->dosenPa->id, $approvalStep1->acted_by);
        $this->assertNotNull($approvalStep1->acted_at);
        $this->assertNotNull($approvalStep1->acted_ip);

        // 2. Kaprodi (Langkah 2) menyetujui
        $responseStep2 = $this->actingAs($this->kaprodiUser)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'setujui',
            'catatan' => 'Disetujui Kaprodi',
        ]);
        $responseStep2->assertRedirect(route('admin.izin.persetujuan.inbox'));

        $this->pengajuan->refresh();
        $this->assertEquals('disetujui', $this->pengajuan->status);
        $this->assertNotNull($this->pengajuan->nomor_surat);
        $this->assertNotNull($this->pengajuan->qr_token);
    }

    public function test_approver_dapat_menolak_pengajuan_dan_mengubah_sisa_langkah_menjadi_dilewati(): void
    {
        // Dosen PA menolak Langkah 1
        $response = $this->actingAs($this->dosenPa)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'tolak',
            'catatan' => 'Berkas tidak lengkap dan mendesak',
        ]);
        $response->assertRedirect(route('admin.izin.persetujuan.inbox'));

        $this->pengajuan->refresh();
        $this->assertEquals('ditolak', $this->pengajuan->status);
        $this->assertEquals('Berkas tidak lengkap dan mendesak', $this->pengajuan->alasan_penolakan);

        $approvalStep2 = IzinApproval::where('pengajuan_izin_id', $this->pengajuan->id)->where('urutan', 2)->first();
        $this->assertEquals('dilewati', $approvalStep2->status);
    }

    public function test_race_condition_dua_approver_memutuskan_bersamaan_satu_berhasil_satu_409(): void
    {
        // Simulasi jika status approval sudah diubah oleh request lain sebelum request ini diproses
        $approvalStep1 = IzinApproval::where('pengajuan_izin_id', $this->pengajuan->id)->where('urutan', 1)->first();
        $approvalStep1->update(['status' => 'disetujui']);
        $this->pengajuan->update(['langkah_aktif' => 2]);

        // Dosen PA mencoba mengirimkan keputusan lagi pada langkah 1 yang sudah disetujui
        $response = $this->actingAs($this->dosenPa)->post(route('admin.izin.persetujuan.putuskan', $this->pengajuan->id), [
            'keputusan' => 'setujui',
        ]);
        $response->assertStatus(409);
    }
}
