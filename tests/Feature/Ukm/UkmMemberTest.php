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
        $ukm = Ukm::create(['nama' => 'UKM Voli Member Test', 'slug' => 'ukm-voli-member-test']);
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);

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
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);

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
        $this->assertSoftDeleted('ukm_members', ['id' => $member->id]);
    }

    /**
     * Penyempurnaan Alur UKM — Isu #1.
     * Reaktivasi anggota (per-user & massal) diperbolehkan HANYA bila UKM-nya
     * berstatus aktif (is_active = true). Jika UKM masih nonaktif, request
     * ditolak untuk mencegah kondisi tidak konsisten (anggota aktif di UKM mati).
     */
    public function test_admin_bisa_mengaktifkan_kembali_anggota_nonaktif_saat_ukm_aktif(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Badminton Test', 'slug' => 'ukm-badminton-test', 'is_active' => true]);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $member = UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.ukm.anggota.aktifkan', [$ukm->id, $member->id]));

        $response->assertRedirect(); 
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('ukm_members', [
            'id' => $member->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_bisa_mengaktifkan_semua_anggota_sekaligus(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Tenis Test', 'slug' => 'ukm-tenis-test', 'is_active' => true]);

        $mhs1 = $this->makeUser(User::USER_ROLE_ID);
        $mhs2 = $this->makeUser(User::USER_ROLE_ID);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs1->id, 'peran' => 'anggota', 'status' => 'nonaktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs2->id, 'peran' => 'anggota', 'status' => 'nonaktif']);

        $response = $this->actingAs($admin)->patch(route('admin.ukm.anggota.aktifkanSemua', $ukm->id));

        $response->assertRedirect(); 
        $response->assertSessionHas('success');
        $this->assertEquals(2, UkmMember::where('ukm_id', $ukm->id)->where('status', 'aktif')->count());
    }

    public function test_reaktivasi_anggota_ditolak_jika_ukm_masih_nonaktif(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Nonaktif Test', 'slug' => 'ukm-nonaktif-test', 'is_active' => false]);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $member = UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'nonaktif',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.ukm.anggota.aktifkan', [$ukm->id, $member->id]));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('ukm_members', [
            'id' => $member->id,
            'status' => 'nonaktif',
        ]);
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

    public function test_pembina_dan_pelatih_bisa_menambah_anggota_jika_ditugaskan(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $pelatih = $this->makeUser(User::PELATIH_UKM_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Staf Test', 'slug' => 'ukm-staf-test']);
        
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $mahasiswa1 = $this->makeUser(User::USER_ROLE_ID);
        $mahasiswa2 = $this->makeUser(User::USER_ROLE_ID);

        $response = $this->actingAs($pembina)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa1->id,
            'peran' => 'anggota',
        ]);
        $response->assertRedirect(); 
        $this->assertDatabaseHas('ukm_members', ['user_id' => $mahasiswa1->id, 'ukm_id' => $ukm->id]);

        $response2 = $this->actingAs($pelatih)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa2->id,
            'peran' => 'anggota',
        ]);
        $response2->assertRedirect();
        $this->assertDatabaseHas('ukm_members', ['user_id' => $mahasiswa2->id, 'ukm_id' => $ukm->id]);
    }

    public function test_staf_lain_ditolak_menambah_anggota_untuk_ukm_bukan_binaannya(): void
    {
        $pembinaLain = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Staf Lain', 'slug' => 'ukm-staf-lain']);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $response = $this->actingAs($pembinaLain)->post(route('admin.ukm.anggota.store', $ukm->id), [
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
        ]);

        $response->assertStatus(403);
    }
}
