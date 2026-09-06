<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\KelasSeeder;

class ApproverResolverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(ProdiSeeder::class);
        $this->seed(KelasSeeder::class);
    }

    public function test_resolve_dosen_pa_fallback_only_returns_dosen_pa_role()
    {
        $resolver = new ApproverResolver();
        
        // Setup users
        $operator = User::factory()->create(['role_id' => User::OPERATOR_ROLE_ID]);
        $dosenPa1 = User::factory()->create(['role_id' => User::DOSEN_PA_ROLE_ID]);
        $dosenPa2 = User::factory()->create(['role_id' => User::DOSEN_PA_ROLE_ID]);
        
        // Student with no dosen_pa_id and no class dosen_pa (fallback scenario)
        $student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'dosen_pa_id' => null,
            'kelas_id' => null,
        ]);

        $candidates = $resolver->resolveStrategy('dosen_pa', null, 'global', null, null, null, $student);

        $this->assertTrue($candidates->isNotEmpty(), 'Fallback harus mengembalikan kandidat');
        foreach ($candidates as $candidate) {
            $this->assertEquals(User::DOSEN_PA_ROLE_ID, $candidate->role_id, 'Kandidat fallback Dosen PA harus ber-role Dosen PA (bukan operator)');
        }
    }

    public function test_resolve_petugas_jaga_fallback_only_returns_pelatih_role()
    {
        $resolver = new ApproverResolver();
        
        // Setup users
        $operator = User::factory()->create(['role_id' => User::OPERATOR_ROLE_ID]);
        $pelatih1 = User::factory()->create(['role_id' => User::PELATIH_ROLE_ID]);
        $pelatih2 = User::factory()->create(['role_id' => User::PELATIH_ROLE_ID]);
        
        // Test with random date where no JadwalPetugas is set
        $candidates = $resolver->resolveStrategy('petugas_jaga', null, 'global', null, null, '2029-01-01', null);

        $this->assertTrue($candidates->isNotEmpty(), 'Fallback petugas jaga harus mengembalikan kandidat');
        foreach ($candidates as $candidate) {
            $this->assertEquals(User::PELATIH_ROLE_ID, $candidate->role_id, 'Kandidat fallback Petugas Jaga harus ber-role Pelatih (bukan operator)');
        }
    }
}
