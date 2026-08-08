<?php

namespace Tests\Feature\Izin;

use App\Models\Pejabat;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PejabatTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $pelatih;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles yang dibutuhkan
        Role::create(['id' => 1, 'name' => 'admin']);
        Role::create(['id' => 2, 'name' => 'operator']);
        Role::create(['id' => 3, 'name' => 'user']);
        Role::create(['id' => 4, 'name' => 'pelatih']);
        Role::create(['id' => 5, 'name' => 'pembina']);

        // User Admin (role_id = 1)
        $this->admin = User::factory()->create([
            'role_id' => 1,
        ]);

        // User Mahasiswa (role_id = 3)
        $this->student = User::factory()->create([
            'role_id' => 3,
        ]);

        // User Pelatih (role_id = 4)
        $this->pelatih = User::factory()->create([
            'role_id' => 4,
        ]);
    }

    public function test_admin_bisa_mengakses_halaman_index_pejabat(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.pejabat.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Pejabat');
    }

    public function test_non_admin_ditolak_mengakses_halaman_index_pejabat(): void
    {
        $response = $this->actingAs($this->student)->get(route('admin.pejabat.index'));
        $response->assertStatus(302); // EnsureUserHasRole redirects non-authorized roles

        $responsePelatih = $this->actingAs($this->pelatih)->get(route('admin.pejabat.index'));
        $responsePelatih->assertStatus(302);
    }

    public function test_admin_bisa_membuat_data_pejabat(): void
    {
        $staf = User::factory()->create(['role_id' => 1]);

        $payload = [
            'user_id' => $staf->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'prodi',
            'lingkup_id' => 1,
            'mulai_menjabat' => '2026-01-01',
            'selesai_menjabat' => '2026-12-31',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.pejabat.store'), $payload);

        $response->assertRedirect(route('admin.pejabat.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pejabats', [
            'user_id' => $staf->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'prodi',
            'lingkup_id' => 1,
            'is_active' => 1,
        ]);
    }

    public function test_non_admin_ditolak_membuat_data_pejabat(): void
    {
        $staf = User::factory()->create(['role_id' => 1]);

        $payload = [
            'user_id' => $staf->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
        ];

        $response = $this->actingAs($this->student)->post(route('admin.pejabat.store'), $payload);
        $response->assertStatus(302);

        $this->assertDatabaseMissing('pejabats', [
            'user_id' => $staf->id,
            'jabatan' => 'kepala_asrama',
        ]);
    }

    public function test_admin_bisa_memperbarui_data_pejabat(): void
    {
        $pejabat = Pejabat::create([
            'user_id' => $this->admin->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        $payload = [
            'user_id' => $this->admin->id,
            'jabatan' => 'unit_kemahasiswaan',
            'lingkup' => 'global',
            'is_active' => 1,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.pejabat.update', $pejabat->id), $payload);

        $response->assertRedirect(route('admin.pejabat.index'));
        $this->assertDatabaseHas('pejabats', [
            'id' => $pejabat->id,
            'jabatan' => 'unit_kemahasiswaan',
        ]);
    }

    public function test_admin_bisa_menghapus_data_pejabat(): void
    {
        $pejabat = Pejabat::create([
            'user_id' => $this->admin->id,
            'jabatan' => 'kepala_asrama',
            'lingkup' => 'global',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.pejabat.destroy', $pejabat->id));

        $response->assertRedirect(route('admin.pejabat.index'));
        $this->assertDatabaseMissing('pejabats', [
            'id' => $pejabat->id,
        ]);
    }
}
