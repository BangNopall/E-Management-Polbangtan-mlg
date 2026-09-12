<?php

namespace Tests\Feature\Izin;

use App\Models\JadwalPetugas;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\Pejabat;
use App\Models\Prodi;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use App\Services\Izin\PengajuanIzinService;
use Carbon\Carbon;
use Database\Seeders\JenisIzinSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class IzinWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected PengajuanIzinService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-01 00:00:00');
        Pejabat::query()->delete();
        $this->seed(JenisIzinSeeder::class);
        $this->service = new PengajuanIzinService(new ApproverResolver());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_happy_path_pengajuan_dan_persetujuan_4_langkah_form_a(): void
    {
        $prodi = Prodi::create(['prodi' => 'TRPL']);
        $dosenPa = User::factory()->create(['name' => 'Dosen PA']);
        $kelas = Kelas::create([
            'nama_kelas' => 'TRPL 1A',
            'kelas' => 'TRPL 1A',
            'prodi_id' => $prodi->id,
            'level_kelas_id' => 1,
            'dosen_pa_id' => $dosenPa->id,
        ]);

        $student = User::factory()->create([
            'kelas_id' => $kelas->id,
            'prodi_id' => $prodi->id,
        ]);

        $ukm = Ukm::create(['nama' => 'Silat', 'slug' => 'silat']);
        $pembina = User::factory()->create(['name' => 'Pembina Silat']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $petugas1 = User::factory()->create(['name' => 'Petugas Piket 1', 'role_id' => User::OPERATOR_ROLE_ID]);
        JadwalPetugas::create(['date' => '2026-08-15', 'petugas1_id' => $petugas1->id]);

        $kaAsrama = User::factory()->create(['name' => 'Ka Asrama']);
        Pejabat::create(['user_id' => $kaAsrama->id, 'jabatan' => 'kepala_asrama', 'lingkup' => 'global', 'is_active' => true]);

        $jenisIzin = JenisIzin::where('kode', 'IZIN_KELUAR')->first();

        // 1. Submit
        $pengajuan = $this->service->ajukan($student, [
            'jenis_izin_id' => $jenisIzin->id,
            'ukm_id' => $ukm->id,
            'keperluan' => 'Lomba Pencak Silat',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => '2026-08-15 08:00:00',
            'waktu_kembali' => '2026-08-15 20:00:00',
        ]);

        $this->assertEquals('diajukan', $pengajuan->status);
        $this->assertEquals(1, $pengajuan->langkah_aktif);
        $this->assertCount(4, $pengajuan->approvals);

        // 2. Approve step 1 (Pembina UKM)
        $approval1 = $pengajuan->approvals->where('urutan', 1)->first();
        $pengajuan = $this->service->setujui($approval1, $pembina);
        $this->assertEquals('diajukan', $pengajuan->status);
        $this->assertEquals(2, $pengajuan->langkah_aktif);

        // 3. Approve step 2 (Dosen PA)
        $approval2 = $pengajuan->approvals->where('urutan', 2)->first();
        $pengajuan = $this->service->setujui($approval2, $dosenPa);
        $this->assertEquals('diajukan', $pengajuan->status);
        $this->assertEquals(3, $pengajuan->langkah_aktif);

        // 4. Approve step 3 (Petugas Piket)
        $approval3 = $pengajuan->approvals->where('urutan', 3)->first();
        $pengajuan = $this->service->setujui($approval3, $petugas1);
        $this->assertEquals('diajukan', $pengajuan->status);
        $this->assertEquals(4, $pengajuan->langkah_aktif);

        // 5. Approve step 4 (Ka Asrama)
        $approval4 = $pengajuan->approvals->where('urutan', 4)->first();
        $pengajuan = $this->service->setujui($approval4, $kaAsrama);

        $this->assertEquals('disetujui', $pengajuan->status);
        $this->assertNull($pengajuan->langkah_aktif);
        $this->assertNotNull($pengajuan->nomor_surat);
        $this->assertNotNull($pengajuan->qr_token);
        $this->assertNotNull($pengajuan->disetujui_at);
    }

    public function test_penolakan_di_langkah_2_menghentikan_workflow(): void
    {
        $prodi = Prodi::create(['prodi' => 'TRPL']);
        $dosenPa = User::factory()->create(['name' => 'Dosen PA']);
        $kelas = Kelas::create(['nama_kelas' => 'TRPL 1A', 'kelas' => 'TRPL 1A', 'prodi_id' => $prodi->id, 'level_kelas_id' => 1, 'dosen_pa_id' => $dosenPa->id]);
        $student = User::factory()->create(['kelas_id' => $kelas->id, 'prodi_id' => $prodi->id]);

        $ukm = Ukm::create(['nama' => 'Silat', 'slug' => 'silat']);
        $pembina = User::factory()->create(['name' => 'Pembina Silat']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $kaAsrama = User::factory()->create(['name' => 'Ka Asrama']);
        Pejabat::create(['user_id' => $kaAsrama->id, 'jabatan' => 'kepala_asrama', 'lingkup' => 'global', 'is_active' => true]);

        $jenisIzin = JenisIzin::where('kode', 'IZIN_KELUAR')->first();

        $pengajuan = $this->service->ajukan($student, [
            'jenis_izin_id' => $jenisIzin->id,
            'ukm_id' => $ukm->id,
            'keperluan' => 'Lomba Silat',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => '2026-08-15 08:00:00',
            'waktu_kembali' => '2026-08-15 20:00:00',
        ]);

        // Step 1 approved
        $approval1 = $pengajuan->approvals->where('urutan', 1)->first();
        $this->service->setujui($approval1, $pembina);

        // Step 2 rejected by Dosen PA
        $approval2 = $pengajuan->approvals->where('urutan', 2)->first();
        $pengajuan = $this->service->tolak($approval2, $dosenPa, 'Jadwal ujian kampus.');

        $this->assertEquals('ditolak', $pengajuan->status);
        $this->assertEquals('Jadwal ujian kampus.', $pengajuan->alasan_penolakan);
        $this->assertNull($pengajuan->langkah_aktif);
        $this->assertEquals('dilewati', $pengajuan->approvals->where('urutan', 3)->first()->status);
        $this->assertEquals('dilewati', $pengajuan->approvals->where('urutan', 4)->first()->status);
    }

    public function test_langkah_kondisi_jika_ada_ukm_dilewati_saat_pengajuan_tanpa_ukm(): void
    {
        $prodi = Prodi::create(['prodi' => 'TRPL']);
        $kaprodi = User::factory()->create(['name' => 'Kaprodi TRPL']);
        Pejabat::create(['user_id' => $kaprodi->id, 'jabatan' => 'kaprodi', 'lingkup' => 'prodi', 'lingkup_id' => $prodi->id, 'is_active' => true]);

        $dosenPa = User::factory()->create(['name' => 'Dosen PA']);
        $kelas = Kelas::create(['nama_kelas' => 'TRPL 1A', 'kelas' => 'TRPL 1A', 'prodi_id' => $prodi->id, 'level_kelas_id' => 1, 'dosen_pa_id' => $dosenPa->id]);
        $student = User::factory()->create(['kelas_id' => $kelas->id, 'prodi_id' => $prodi->id]);

        $kaAsrama = User::factory()->create(['name' => 'Ka Asrama']);
        Pejabat::create(['user_id' => $kaAsrama->id, 'jabatan' => 'kepala_asrama', 'lingkup' => 'global', 'is_active' => true]);

        $jenisIzin = JenisIzin::where('kode', 'IB')->first(); // IB tidak butuh UKM

        $pengajuan = $this->service->ajukan($student, [
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Izin Bermalam Pulang Kampung',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => '2026-08-20 08:00:00',
            'waktu_kembali' => '2026-08-22 18:00:00',
        ]);

        $this->assertEquals('diajukan', $pengajuan->status);
        $this->assertEquals(1, $pengajuan->langkah_aktif);
        $this->assertEquals('menunggu', $pengajuan->approvals->where('urutan', 1)->first()->status);
    }

    public function test_submit_ditolak_saat_rantai_tidak_ter_resolve(): void
    {
        $prodi = Prodi::create(['prodi' => 'Prodi Tanpa Kaprodi']);
        // Tidak ada Kaprodi terdaftar untuk prodi Tanpa Kaprodi!
        $student = User::factory()->create(['prodi_id' => $prodi->id]);

        $jenisIzin = JenisIzin::where('kode', 'IB')->first();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Rantai penandatangan untuk 'Menyetujui, Ketua Program Studi' tidak dapat ditemukan");

        $this->service->ajukan($student, [
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Pulang',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => '2026-08-20 08:00:00',
            'waktu_kembali' => '2026-08-22 18:00:00',
        ]);
    }
}
