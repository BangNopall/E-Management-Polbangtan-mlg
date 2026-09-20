<?php

namespace Tests\Feature\Izin;

use App\Models\JadwalPetugas;
use App\Models\Kelas;
use App\Models\Pejabat;
use App\Models\Prodi;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use App\Models\IzinWorkflowStep;
use App\Services\Izin\ApproverResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinResolverTest extends TestCase
{
    use RefreshDatabase;

    protected ApproverResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        Pejabat::query()->delete();
        $this->resolver = new ApproverResolver();
    }

    public function test_resolver_pejabat_global_dan_prodi(): void
    {
        $prodi = Prodi::create(['prodi' => 'TRPL']);

        $userGlobal = User::factory()->create(['name' => 'Ka Asrama']);
        Pejabat::create([
            'user_id' => $userGlobal->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        $userKaprodi = User::factory()->create(['name' => 'Kaprodi TRPL']);
        Pejabat::create([
            'user_id' => $userKaprodi->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'prodi',
            'lingkup_id' => $prodi->id,
            'is_active' => true,
        ]);

        $student = User::factory()->create(['prodi_id' => $prodi->id]);

        $stepGlobal = new IzinWorkflowStep([
            'resolver' => 'pejabat',
            'jabatan' => ['kepala_asrama'],
            'lingkup' => 'global',
        ]);

        $resGlobal = $this->resolver->resolve($stepGlobal, ['user' => $student]);
        $this->assertCount(1, $resGlobal['candidates']);
        $this->assertEquals($userGlobal->id, $resGlobal['candidates']->first()->id);

        $stepProdi = new IzinWorkflowStep([
            'resolver' => 'pejabat',
            'jabatan' => ['kaprodi'],
            'lingkup' => 'prodi',
        ]);

        $resProdi = $this->resolver->resolve($stepProdi, ['user' => $student]);
        $this->assertCount(1, $resProdi['candidates']);
        $this->assertEquals($userKaprodi->id, $resProdi['candidates']->first()->id);
    }

    public function test_resolver_dosen_pa(): void
    {
        $dosenPa = User::factory()->create(['name' => 'Pak Dosen']);
        $kelas = Kelas::create([
            'nama_kelas' => 'TRPL 1A',
            'kelas' => 'TRPL 1A',
            'prodi_id' => 1,
            'level_kelas_id' => 1,
            'dosen_pa_id' => $dosenPa->id,
        ]);

        $student = User::factory()->create(['kelas_id' => $kelas->id]);

        $step = new IzinWorkflowStep([
            'resolver' => 'dosen_pa',
        ]);

        $res = $this->resolver->resolve($step, ['user' => $student]);
        $this->assertCount(1, $res['candidates']);
        $this->assertEquals($dosenPa->id, $res['candidates']->first()->id);
    }

    public function test_resolver_dosen_pa_null_fallback_ke_kaprodi(): void
    {
        $prodi = Prodi::create(['prodi' => 'TRPL']);
        $kelas = Kelas::create([
            'nama_kelas' => 'TRPL 1B',
            'kelas' => 'TRPL 1B',
            'prodi_id' => $prodi->id,
            'level_kelas_id' => 1,
            'dosen_pa_id' => null, // null Dosen PA
        ]);

        $student = User::factory()->create([
            'kelas_id' => $kelas->id,
            'prodi_id' => $prodi->id,
        ]);

        $kaprodi = User::factory()->create(['name' => 'Kaprodi TRPL']);
        Pejabat::create([
            'user_id' => $kaprodi->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'prodi',
            'lingkup_id' => $prodi->id,
            'is_active' => true,
        ]);

        $step = new IzinWorkflowStep([
            'resolver' => 'dosen_pa',
            'fallback_resolver' => 'pejabat',
            'fallback_jabatan' => ['kaprodi'],
            'lingkup' => 'prodi',
        ]);

        $res = $this->resolver->resolve($step, ['user' => $student]);

        $this->assertTrue($res['is_fallback']);
        $this->assertCount(1, $res['candidates']);
        $this->assertEquals($kaprodi->id, $res['candidates']->first()->id);
    }

    public function test_resolver_pembina_ukm(): void
    {
        $ukm = Ukm::create([
            'nama' => 'Pencak Silat',
            'slug' => 'pencak-silat',
        ]);
        $pembina = User::factory()->create(['name' => 'Pembina Silat']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $pembina->id,
            'peran' => 'pembina',
            'status' => 'aktif',
        ]);

        $student = User::factory()->create();

        $step = new IzinWorkflowStep([
            'resolver' => 'pembina_ukm',
        ]);

        $res = $this->resolver->resolve($step, [
            'user' => $student,
            'ukm_id' => $ukm->id,
        ]);

        $this->assertCount(1, $res['candidates']);
        $this->assertEquals($pembina->id, $res['candidates']->first()->id);
    }

    public function test_resolver_petugas_jaga(): void
    {
        $petugas1 = User::factory()->create(['name' => 'Petugas 1', 'role_id' => User::OPERATOR_ROLE_ID]);
        $petugas2 = User::factory()->create(['name' => 'Petugas 2', 'role_id' => User::OPERATOR_ROLE_ID]);

        JadwalPetugas::create([
            'date' => '2026-08-10',
            'petugas1_id' => $petugas1->id,
            'petugas2_id' => $petugas2->id,
        ]);

        $student = User::factory()->create();

        $step = new IzinWorkflowStep([
            'resolver' => 'petugas_jaga',
        ]);

        $res = $this->resolver->resolve($step, [
            'user' => $student,
            'waktu_berangkat' => '2026-08-10',
        ]);

        $this->assertCount(2, $res['candidates']);
        $this->assertEquals([$petugas1->id, $petugas2->id], $res['candidates']->pluck('id')->all());
    }

    public function test_resolver_petugas_jaga_fallback_ke_role_operator_jika_jadwal_null(): void
    {
        $operator = User::factory()->create([
            'name' => 'Operator Asrama',
            'role_id' => User::OPERATOR_ROLE_ID,
        ]);

        $student = User::factory()->create();

        $step = new IzinWorkflowStep([
            'resolver' => 'petugas_jaga',
        ]);

        // Tidak ada JadwalPetugas untuk tanggal ini
        $res = $this->resolver->resolve($step, [
            'user' => $student,
            'waktu_berangkat' => '2026-08-15',
        ]);

        $this->assertNotEmpty($res['candidates']);
        $this->assertTrue($res['candidates']->contains('id', $operator->id));
        foreach ($res['candidates'] as $candidate) {
            $this->assertEquals(User::OPERATOR_ROLE_ID, $candidate->role_id);
        }
    }

    public function test_resolver_dosen_pa_fallback_ke_role_dosen_pa_jika_dosen_pa_id_null(): void
    {
        $dosenPaUser = User::factory()->create([
            'name' => 'Staf Dosen PA',
            'role_id' => User::DOSEN_PA_ROLE_ID,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'TRPL 1C',
            'kelas' => 'TRPL 1C',
            'prodi_id' => 1,
            'level_kelas_id' => 1,
            'dosen_pa_id' => null,
        ]);

        $student = User::factory()->create(['kelas_id' => $kelas->id]);

        $step = new IzinWorkflowStep([
            'resolver' => 'dosen_pa',
            'fallback_resolver' => null,
        ]);

        $res = $this->resolver->resolve($step, ['user' => $student]);

        $this->assertNotEmpty($res['candidates']);
        $this->assertTrue($res['candidates']->contains('id', $dosenPaUser->id));
    }

    public function test_resolver_kandidat_kosong_tanpa_fallback(): void
    {
        $student = User::factory()->create();

        $step = new IzinWorkflowStep([
            'resolver' => 'pembina_ukm',
            'fallback_resolver' => null,
        ]);

        $res = $this->resolver->resolve($step, ['user' => $student]);

        $this->assertEmpty($res['candidates']);
        $this->assertFalse($res['is_fallback']);
    }
}
