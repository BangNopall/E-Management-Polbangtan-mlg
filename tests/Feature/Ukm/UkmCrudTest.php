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

    public function test_pelatih_ukm_binaannya_bisa_melihat_detail_ukm(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Panjat Tebing', 'slug' => 'ukm-panjat-tebing']);
        \App\Models\UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $response = $this->actingAs($pelatih)->get(route('admin.ukm.show', $ukm->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ukm.show');
    }

    public function test_pelatih_ukm_lain_ditolak_melihat_detail_ukm_bukan_binaannya(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukmLain = Ukm::create(['nama' => 'UKM Memanah', 'slug' => 'ukm-memanah']);
        $ukmTarget = Ukm::create(['nama' => 'UKM Karate', 'slug' => 'ukm-karate']);
        \App\Models\UkmMember::create(['ukm_id' => $ukmLain->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $response = $this->actingAs($pelatih)->get(route('admin.ukm.show', $ukmTarget->id));

        $response->assertStatus(403);
    }

    /**
     * Perbaikan Modul UKM Dinamis — Isu #5.
     * Menonaktifkan UKM harus meng-cascade status 'nonaktif' ke seluruh
     * ukm_members yang sebelumnya aktif (via app/Observers/UkmObserver.php).
     * Sesuai keputusan user: TIDAK ada auto-reaktivasi saat UKM diaktifkan
     * kembali — admin mengelola ulang status anggota secara manual.
     */
    public function test_menonaktifkan_ukm_mencascade_status_nonaktif_ke_semua_anggota(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Cascade Test', 'slug' => 'ukm-cascade-test', 'is_active' => true]);

        $mhs = $this->makeUser(User::USER_ROLE_ID);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $memberMhs = \App\Models\UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs->id, 'peran' => 'anggota', 'status' => 'aktif']);
        $memberPelatih = \App\Models\UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $this->actingAs($admin)->put(route('admin.ukm.update', $ukm->id), [
            'nama' => 'UKM Cascade Test',
            'is_active' => 0,
        ]);

        $this->assertDatabaseHas('ukm_members', ['id' => $memberMhs->id, 'status' => 'nonaktif']);
        $this->assertDatabaseHas('ukm_members', ['id' => $memberPelatih->id, 'status' => 'nonaktif']);
    }

    public function test_mengaktifkan_kembali_ukm_tidak_mengaktifkan_ulang_anggota_secara_otomatis(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Reaktivasi Test', 'slug' => 'ukm-reaktivasi-test', 'is_active' => true]);
        $mhs = $this->makeUser(User::USER_ROLE_ID);
        $member = \App\Models\UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs->id, 'peran' => 'anggota', 'status' => 'aktif']);

        // Deactivate UKM -> cascades member to nonaktif
        $this->actingAs($admin)->put(route('admin.ukm.update', $ukm->id), [
            'nama' => 'UKM Reaktivasi Test',
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('ukm_members', ['id' => $member->id, 'status' => 'nonaktif']);

        // Reactivate UKM -> member status should stay nonaktif (manual admin action required)
        $this->actingAs($admin)->put(route('admin.ukm.update', $ukm->id), [
            'nama' => 'UKM Reaktivasi Test',
            'is_active' => 1,
        ]);
        $this->assertDatabaseHas('ukm_members', ['id' => $member->id, 'status' => 'nonaktif']);
    }

    /**
     * Perbaikan Modul UKM Dinamis — Isu #6.
     * Admin bisa menghapus UKM secara permanen HANYA saat UKM sudah
     * dinonaktifkan (syarat UI di ukm_table.blade.php). Cascade delete
     * ke ukm_members/ukm_jadwals sudah dijamin oleh FK cascadeOnDelete().
     */
    public function test_admin_bisa_menghapus_ukm_nonaktif_secara_permanen(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Hapus Test', 'slug' => 'ukm-hapus-test', 'is_active' => false]);
        $mhs = $this->makeUser(User::USER_ROLE_ID);
        \App\Models\UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mhs->id, 'peran' => 'anggota', 'status' => 'nonaktif']);

        $response = $this->actingAs($admin)->delete(route('admin.ukm.destroy', $ukm->id));

        $response->assertRedirect(route('admin.ukm.index'));
        $this->assertDatabaseMissing('ukms', ['id' => $ukm->id]);
        $this->assertDatabaseMissing('ukm_members', ['ukm_id' => $ukm->id]);
    }

    /**
     * Penyempurnaan Alur UKM — Isu #8.
     * Halaman index UKM harus memaginasi 20 data per halaman (sebelumnya 10).
     */
    public function test_daftar_ukm_memaginasi_20_data_per_halaman(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);

        for ($i = 1; $i <= 25; $i++) {
            Ukm::create([
                'nama' => 'UKM Paginasi ' . $i,
                'slug' => 'ukm-paginasi-' . $i,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.ukm.index'));

        $response->assertStatus(200);
        $response->assertViewHas('ukms', function ($ukms) {
            return $ukms->perPage() === 20 && $ukms->count() === 20 && $ukms->total() >= 25;
        });
    }

    public function test_pelatih_tidak_bisa_menghapus_ukm(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Hapus Ditolak', 'slug' => 'ukm-hapus-ditolak', 'is_active' => false]);

        $response = $this->actingAs($pelatih)->delete(route('admin.ukm.destroy', $ukm->id));

        $response->assertStatus(302);
        $this->assertDatabaseHas('ukms', ['id' => $ukm->id]);
    }
}
