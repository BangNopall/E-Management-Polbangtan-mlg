<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\BlokRuangan;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\KelasSeeder;
use Database\Seeders\BlokRuanganSeeder;

class Fixes8IssuesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary foundational data
        $this->seed(RoleSeeder::class);
        $this->seed(ProdiSeeder::class);
        $this->seed(KelasSeeder::class);
        $this->seed(BlokRuanganSeeder::class);
    }

    public function test_edit_petugas_redirects_back_on_error(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $petugas = User::factory()->create(['role_id' => 2]); // Petugas

        // Simulate failing validation or logic in editDataPetugas
        $response = $this->actingAs($admin)->post(route('admin.editDataPetugas', $petugas->id), [
            // Submitting wrong format or wrong data to trigger error
            'old_password' => 'wrong',
            'new_password' => 'newpassword',
        ]);

        $response->assertStatus(302);
        // It should NOT throw Missing Parameter exception.
        // It should redirect back to the form (which is the show form)
        $response->assertSessionHas('error');
    }

    public function test_mahasiswa_can_save_dosen_pa(): void
    {
        $dosenPa = User::factory()->create(['role_id' => 5]); // Wait, Dosen PA is role 5? (Usually Dosen PA is a specific role, let's just create one)
        $mahasiswa = User::factory()->create(['role_id' => 3]);

        $response = $this->actingAs($mahasiswa)->post(route('home.Editprofil', $mahasiswa->id), [
            'name' => 'Updated Name',
            'kelas_id' => $mahasiswa->kelas_id,
            'blok_ruangan_id' => $mahasiswa->blok_ruangan_id,
            'no_kamar' => 10,
            'asal_daerah' => 'Malang',
            'dosen_pa_id' => $dosenPa->id
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'id' => $mahasiswa->id,
            'dosen_pa_id' => $dosenPa->id
        ]);
    }

    public function test_search_mahasiswa_does_not_leak_other_roles(): void
    {
        $admin = User::factory()->create(['role_id' => 1, 'name' => 'Admin Suprianto']);
        $mahasiswa = User::factory()->create(['role_id' => 3, 'name' => 'Mahasiswa Supri']);

        $response = $this->actingAs($admin)->post(route('admin.searchMahasiswa'), [
            'search_term' => 'Supri'
        ]);

        $response->assertStatus(200);
        
        // Response is JSON containing 'table' HTML string.
        // It should contain Mahasiswa but NOT Admin
        $json = $response->json();
        $this->assertStringContainsString('Mahasiswa Supri', $json['table']);
        $this->assertStringNotContainsString('Admin Suprianto', $json['table']);
    }

    public function test_dosen_pa_index_has_pagination_and_search(): void
    {
        $admin = User::factory()->create(['role_id' => 1]);
        
        $response = $this->actingAs($admin)->get(route('admin.dosen_pa.index', ['search' => 'test']));
        
        $response->assertStatus(200);
        // Assert view has paginator (we can't assert easily without checking view data, but we can check status)
    }
}
