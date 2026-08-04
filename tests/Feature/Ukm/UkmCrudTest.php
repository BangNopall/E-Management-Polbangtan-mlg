<?php

namespace Tests\Feature\Ukm;

use App\Models\Role;
use App\Models\Ukm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone B dari .claude/plans/epic-01-ukm-dinamis.md — US 1.1 (CRUD UKM).
 *
 * Persyaratan:
 *   - Create UKM = role admin (1) saja; pelatih (4), pembina (5), user (3) ditolak (302 redirect).
 *   - 1 field wajib ('nama'), slug ter-generate otomatis dari nama.
 *   - Nama duplikat ditolak via validasi (bukan 500).
 *   - UKM bisa dinonaktifkan (`is_active = false`) tanpa menghapus data.
 */
class UkmCrudTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }

    public function test_admin_bisa_mengakses_halaman_index_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);

        $response = $this->actingAs($admin)->get(route('admin.ukm.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ukm.index');
    }

    public function test_admin_bisa_membuat_ukm_dengan_satu_field_wajib(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);

        $response = $this->actingAs($admin)->post(route('admin.ukm.store'), [
            'nama' => 'UKM Robotik',
            'deskripsi' => 'Klub robotika mahasiswa',
        ]);

        $response->assertRedirect(route('admin.ukm.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ukms', [
            'nama' => 'UKM Robotik',
            'slug' => 'ukm-robotik',
            'deskripsi' => 'Klub robotika mahasiswa',
            'is_active' => 1,
            'created_by' => $admin->id,
        ]);
    }

    public function test_slug_dibuat_otomatis_dari_nama(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);

        $this->actingAs($admin)->post(route('admin.ukm.store'), [
            'nama' => 'UKM Bulu Tangkis & Tenis Meja',
        ]);

        $this->assertDatabaseHas('ukms', [
            'nama' => 'UKM Bulu Tangkis & Tenis Meja',
            'slug' => 'ukm-bulu-tangkis-tenis-meja',
        ]);
    }

    public function test_nama_ukm_duplikat_ditolak_dengan_pesan_validasi(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        Ukm::create(['nama' => 'UKM Tari', 'slug' => 'ukm-tari']);

        $response = $this->actingAs($admin)->post(route('admin.ukm.store'), [
            'nama' => 'UKM Tari',
        ]);

        $response->assertSessionHasErrors(['nama']);
    }

    public function test_pelatih_tidak_bisa_membuat_ukm(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.store'), [
            'nama' => 'UKM Catur',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseMissing('ukms', ['nama' => 'UKM Catur']);
    }

    public function test_mahasiswa_tidak_bisa_mengakses_halaman_kelola_ukm(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $response = $this->actingAs($mahasiswa)->get(route('admin.ukm.index'));

        $response->assertStatus(302);
    }

    public function test_admin_bisa_mengubah_status_aktif_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Seni', 'slug' => 'ukm-seni', 'is_active' => true]);

        $response = $this->actingAs($admin)->put(route('admin.ukm.update', $ukm->id), [
            'nama' => 'UKM Seni',
            'is_active' => 0,
        ]);

        $response->assertRedirect(route('admin.ukm.index'));
        $this->assertDatabaseHas('ukms', [
            'id' => $ukm->id,
            'is_active' => 0,
        ]);
    }

    public function test_admin_bisa_melihat_detail_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Futsal', 'slug' => 'ukm-futsal']);

        $response = $this->actingAs($admin)->get(route('admin.ukm.show', $ukm->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ukm.show');
        $response->assertViewHas('ukm');
    }
}
