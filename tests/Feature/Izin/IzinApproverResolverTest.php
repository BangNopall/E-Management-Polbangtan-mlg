<?php

namespace Tests\Feature\Izin;

use App\Models\JenisIzin;
use App\Models\Pejabat;
use App\Models\PengajuanIzin;
use App\Models\PengajuanIzinWorkflowStep;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use Carbon\Carbon;
use Database\Seeders\KelasSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinApproverResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-01 08:00:00'));

        if (\App\Models\Role::count() === 0) {
            $this->seed(RoleSeeder::class);
        }
        if (\App\Models\Prodi::count() === 0) {
            $this->seed(ProdiSeeder::class);
        }
        if (\App\Models\Kelas::count() === 0) {
            $this->seed(KelasSeeder::class);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_fallback_dosen_pa_ke_pejabat_kaprodi_aktif_saat_dosen_pa_kosong(): void
    {
        Pejabat::query()->delete();

        $resolver = new ApproverResolver();

        // Setup Kaprodi user
        $kaprodiUser = User::factory()->create([
            'role_id' => User::PEJABAT_ROLE_ID,
            'name' => 'Kaprodi Pertanian',
        ]);

        Pejabat::create([
            'user_id' => $kaprodiUser->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        // Student with no dosen_pa assigned
        $student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'dosen_pa_id' => null,
            'kelas_id' => null,
        ]);

        $jenisIzin = JenisIzin::create([
            'nama' => 'Izin Dinas',
            'kode' => 'ID',
            'is_active' => true,
        ]);

        $pengajuan = PengajuanIzin::create([
            'user_id' => $student->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Dinas Luar',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => now()->addDay(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $student->name,
            'status' => 'diajukan',
        ]);

        $step = new \App\Models\IzinWorkflowStep([
            'jenis_izin_id' => $jenisIzin->id,
            'urutan' => 2,
            'nama_langkah' => 'Dosen PA',
            'resolver' => 'dosen_pa',
            'fallback_resolver' => 'pejabat',
            'fallback_jabatan' => ['kaprodi'],
            'fallback_lingkup' => 'global',
            'tipe_persetujuan' => 'semua',
        ]);

        $result = $resolver->resolve($step, $pengajuan);

        // When student has no assigned Dosen PA, it must fall back to Kaprodi
        $this->assertTrue($result['is_fallback'], 'Harus menggunakan fallback resolver');
        $this->assertTrue($result['candidates']->pluck('id')->contains($kaprodiUser->id), 'Kaprodi harus ada dalam kandidat approver');
    }

    public function test_semua_kandidat_approver_bisa_melihat_izin_di_inbox(): void
    {
        $pejabatUserA = User::factory()->create(['role_id' => User::PEJABAT_ROLE_ID]);
        $pejabatUserB = User::factory()->create(['role_id' => User::PEJABAT_ROLE_ID]);

        Pejabat::create([
            'user_id' => $pejabatUserA->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        Pejabat::create([
            'user_id' => $pejabatUserB->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        $student = User::factory()->create(['role_id' => User::USER_ROLE_ID]);
        $jenisIzin = JenisIzin::create([
            'nama' => 'Izin Pulang',
            'kode' => 'IP',
            'is_active' => true,
        ]);

        $pengajuan = PengajuanIzin::create([
            'user_id' => $student->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Pulang Kampung',
            'tujuan_lokasi' => 'Kediri',
            'waktu_berangkat' => now()->addDay(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $student->name,
            'status' => 'diajukan',
            'langkah_aktif' => 1,
        ]);

        // Approval record created with first candidate (Pejabat A)
        \App\Models\IzinApproval::create([
            'pengajuan_izin_id' => $pengajuan->id,
            'urutan' => 1,
            'label_snapshot' => 'Kepala Asrama',
            'approver_user_id' => $pejabatUserA->id,
            'status' => 'menunggu',
        ]);

        // Pejabat B (co-approver with same role/jurisdiction) accesses inbox
        $response = $this->actingAs($pejabatUserB)->get(route('admin.izin.persetujuan.inbox'));
        $response->assertOk();

        // Pejabat B must see the pending approval in their inbox list
        $response->assertSee('Pulang Kampung');
    }
}
