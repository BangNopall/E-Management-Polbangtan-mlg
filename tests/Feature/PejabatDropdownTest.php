<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\KelasSeeder;
use Database\Seeders\BlokRuanganSeeder;

class PejabatDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(ProdiSeeder::class);
        $this->seed(KelasSeeder::class);
        $this->seed(BlokRuanganSeeder::class);
    }

    public function test_pejabat_dropdown_only_contains_pejabat_role(): void
    {
        $adminUser = User::factory()->create(['role_id' => User::ADMIN_ROLE_ID, 'name' => 'Admin Test']);
        $operatorUser = User::factory()->create(['role_id' => User::OPERATOR_ROLE_ID, 'name' => 'Operator Test']);
        $pejabatUser = User::factory()->create(['role_id' => User::PEJABAT_ROLE_ID, 'name' => 'Pejabat Test']);
        
        $response = $this->actingAs($adminUser)->get(route('admin.pejabat.index'));
        
        $response->assertStatus(200);
        
        // Assert that the 'users' variable passed to the view contains only the pejabat user
        $response->assertViewHas('users', function ($users) use ($pejabatUser, $adminUser, $operatorUser) {
            return $users->contains('id', $pejabatUser->id) && 
                   !$users->contains('id', $adminUser->id) && 
                   !$users->contains('id', $operatorUser->id);
        });
    }
}
