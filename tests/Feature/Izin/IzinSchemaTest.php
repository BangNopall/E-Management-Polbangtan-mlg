<?php

namespace Tests\Feature\Izin;

use App\Models\IzinApproval;
use App\Models\IzinWorkflowStep;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinSchemaTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId = 3): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }

    public function test_kode_jenis_izin_harus_unik(): void
    {
        JenisIzin::create(['kode' => 'IZIN_KELUAR', 'nama' => 'Izin Keluar Asrama']);

        $this->expectException(QueryException::class);

        JenisIzin::create(['kode' => 'IZIN_KELUAR', 'nama' => 'Izin Keluar Duplikat']);
    }

    public function test_urutan_step_harus_unik_per_jenis_izin(): void
    {
        $jenis = JenisIzin::create(['kode' => 'IB', 'nama' => 'Izin Bermalam']);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $jenis->id,
            'urutan' => 1,
            'label' => 'Step 1',
            'resolver' => 'pejabat',
            'jabatan' => ['kaprodi'],
        ]);

        $this->expectException(QueryException::class);

        IzinWorkflowStep::create([
            'jenis_izin_id' => $jenis->id,
            'urutan' => 1,
            'label' => 'Step 1 Duplikat',
            'resolver' => 'dosen_pa',
        ]);
    }

    public function test_qr_token_pengajuan_izin_harus_unik(): void
    {
        $jenis = JenisIzin::create(['kode' => 'DELEGASI', 'nama' => 'Izin Delegasi']);
        $user = $this->makeUser();

        // 40 chars
        $token = 'TOKEN12345678901234567890123456789012345';

        PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Lomba',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now(),
            'waktu_kembali' => now()->addHours(12),
            'nama_snapshot' => $user->name,
            'qr_token' => $token,
        ]);

        $this->expectException(QueryException::class);

        PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Lomba 2',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now(),
            'waktu_kembali' => now()->addHours(12),
            'nama_snapshot' => $user->name,
            'qr_token' => $token,
        ]);
    }

    public function test_urutan_approval_harus_unik_per_pengajuan_izin(): void
    {
        $jenis = JenisIzin::create(['kode' => 'IZIN_KELUAR', 'nama' => 'Izin Keluar']);
        $user = $this->makeUser();
        $pengajuan = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Beli Obat',
            'tujuan_lokasi' => 'Apotek',
            'waktu_berangkat' => now(),
            'waktu_kembali' => now()->addHours(2),
            'nama_snapshot' => $user->name,
        ]);

        IzinApproval::create([
            'pengajuan_izin_id' => $pengajuan->id,
            'urutan' => 1,
            'label_snapshot' => 'Pembina UKM',
            'status' => 'menunggu',
        ]);

        $this->expectException(QueryException::class);

        IzinApproval::create([
            'pengajuan_izin_id' => $pengajuan->id,
            'urutan' => 1,
            'label_snapshot' => 'Dosen PA',
            'status' => 'menunggu',
        ]);
    }

    public function test_menghapus_jenis_izin_mencascade_workflow_steps(): void
    {
        $jenis = JenisIzin::create(['kode' => 'TES_CASCADE', 'nama' => 'Tes Cascade']);
        $step = IzinWorkflowStep::create([
            'jenis_izin_id' => $jenis->id,
            'urutan' => 1,
            'label' => 'Step 1',
            'resolver' => 'pejabat',
            'jabatan' => ['kepala_asrama'],
        ]);

        $jenis->delete();

        $this->assertDatabaseMissing('izin_workflow_steps', ['id' => $step->id]);
    }

    public function test_menghapus_pengajuan_izin_mencascade_approvals(): void
    {
        $jenis = JenisIzin::create(['kode' => 'TES_CASCADE_2', 'nama' => 'Tes Cascade 2']);
        $user = $this->makeUser();
        $pengajuan = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Pulang',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => now(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $user->name,
        ]);

        $approval = IzinApproval::create([
            'pengajuan_izin_id' => $pengajuan->id,
            'urutan' => 1,
            'label_snapshot' => 'Kaprodi',
            'status' => 'menunggu',
        ]);

        $pengajuan->delete();

        $this->assertDatabaseMissing('izin_approvals', ['id' => $approval->id]);
    }

    public function test_scope_aktif_pada_dan_belum_kembali(): void
    {
        $jenis = JenisIzin::create(['kode' => 'SCOPE_TEST', 'nama' => 'Scope Test']);
        $user = $this->makeUser();

        $berangkat = now()->subHour();
        $kembali = now()->addHours(3);

        $pengajuanAktif = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Acara Keluarga',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => $berangkat,
            'waktu_kembali' => $kembali,
            'nama_snapshot' => $user->name,
            'status' => 'berjalan',
        ]);

        $pengajuanDraft = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $jenis->id,
            'keperluan' => 'Belum Disetujui',
            'tujuan_lokasi' => 'Batu',
            'waktu_berangkat' => $berangkat,
            'waktu_kembali' => $kembali,
            'nama_snapshot' => $user->name,
            'status' => 'draft',
        ]);

        $hasilAktif = PengajuanIzin::aktifPada(now())->get();
        $this->assertTrue($hasilAktif->contains($pengajuanAktif->id));
        $this->assertFalse($hasilAktif->contains($pengajuanDraft->id));

        $hasilBelumKembali = PengajuanIzin::belumKembali()->get();
        $this->assertTrue($hasilBelumKembali->contains($pengajuanAktif->id));
    }
}
