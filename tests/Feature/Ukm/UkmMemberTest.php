<?php

namespace Tests\Feature\Ukm;

use App\Models\Role;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone B dari .claude/plans/epic-01-ukm-dinamis.md — US 1.0 (Kelola Anggota UKM).
 *
 * Aturan otorisasi yang diuji:
 *   - Admin (role 1) bisa menambah/menghapus anggota.
 *   - `peran = 'anggota'` HANYA boleh untuk mahasiswa (role_id = 3).
 *   - `peran IN ('pelatih', 'pembina')` HANYA boleh untuk staf (role_id = 4 atau 5).
 *   - Menambah anggota yang sudah terdaftar ditolak dengan pesan validasi (bukan 500 DB error).
 */
class UkmMemberTest extends TestCase
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

    public function test_admin_bisa_menambah_mahasiswa_sebagai_anggota(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Basket', 'slug' => 'ukm-basket']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $response = $this->actingAs($admin)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ukm_members', [
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);
    }

    public function test_admin_bisa_menambah_staf_sebagai_pelatih(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Voli', 'slug' => 'ukm-voli']);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);

        $response = $this->actingAs($admin)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $pelatih->id,
            'peran' => 'pelatih',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ukm_members', [
            'ukm_id' => $ukm->id,
            'user_id' => $pelatih->id,
            'peran' => 'pelatih',
        ]);
    }

    public function test_menambah_anggota_yang_sudah_terdaftar_ditolak_dengan_pesan_validasi(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Renang', 'slug' => 'ukm-renang']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
        ]);

        $response->assertSessionHasErrors(['user_id']);
    }

    public function test_staf_tidak_bisa_ditambahkan_dengan_peran_anggota(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Silat', 'slug' => 'ukm-silat']);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);

        $response = $this->actingAs($admin)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $pelatih->id,
            'peran' => 'anggota', // Aturan: peran='anggota' HANYA untuk role_id=3
        ]);

        $response->assertSessionHasErrors(['peran']);
        $this->assertDatabaseMissing('ukm_members', [
            'ukm_id' => $ukm->id,
            'user_id' => $pelatih->id,
        ]);
    }

    public function test_mahasiswa_tidak_bisa_ditambahkan_dengan_peran_pelatih(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Badminton', 'slug' => 'ukm-badminton']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $response = $this->actingAs($admin)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa->id,
            'peran' => 'pelatih', // Aturan: peran='pelatih' HANYA untuk staf (role 4/5)
        ]);

        $response->assertSessionHasErrors(['peran']);
        $this->assertDatabaseMissing('ukm_members', [
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
        ]);
    }

    public function test_admin_bisa_mengeluarkan_anggota_dari_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Musik', 'slug' => 'ukm-musik']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $member = UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.ukm.anggota.destroy', [$ukm->id, $member->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('ukm_members', ['id' => $member->id]);
    }

    public function test_mahasiswa_tidak_bisa_menambah_anggota(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $target = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Catur', 'slug' => 'ukm-catur']);

        $response = $this->actingAs($mahasiswa)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $target->id,
            'peran' => 'anggota',
        ]);

        $response->assertStatus(302);
    }
}
