<?php

namespace Tests\Feature\Ukm;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone A dari .claude/plans/epic-01-ukm-dinamis.md — Konflik 1.
 *
 * Menguji regresi 2 titik hardcode role ID yang ditemukan saat audit desain
 * (lihat plan §"Konflik 1"):
 *   1. AuthController::authDashboard() — role 5 tidak match apa pun => halaman kosong.
 *   2. routes/web.php — grup admin.index digate 'role:admin,operator,pelatih',
 *      pembina gagal masuk => EnsureUserHasRole redirect ke admin.index => loop.
 *
 * test_role_lain_tetap_diarahkan_seperti_sebelumnya mengunci perilaku 4 role
 * lama SEBELUM authDashboard() diubah, supaya perubahan tidak merusak alur
 * login harian admin/operator/pelatih/mahasiswa.
 */
class PembinaRoleTest extends TestCase
{
    use RefreshDatabase;

    // Role 1-5 datang dari DatabaseSeeder (Tests\TestCase::$seed = true membuat
    // RefreshDatabase menjalankan migrate:fresh --seed sekali untuk seluruh
    // proses tes) — bukan di-hardcode manual di sini. RoleSeeder.php sendiri
    // tetap tidak disentuh; pembina ditambahkan lewat DatabaseSeeder.php (A3).

    public function test_pembina_diarahkan_ke_dashboard_admin_setelah_login(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->get('/');

        $response->assertRedirect(route('admin.index'));
    }

    public function test_pembina_tidak_terjebak_redirect_loop_saat_membuka_dashboard_admin(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->get('/dashboard-admin');

        $response->assertStatus(200);
    }

    public function test_role_lain_tetap_diarahkan_seperti_sebelumnya(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $operator = $this->makeUser(User::OPERATOR_ROLE_ID);
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);

        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.index'));
        $this->actingAs($operator)->get('/')->assertRedirect(route('admin.index'));
        $this->actingAs($pelatih)->get('/')->assertRedirect(route('admin.index'));
        $this->actingAs($mahasiswa)->get('/')->assertRedirect(route('home.index'));
    }

    public function test_role_pembina_tersedia_di_tabel_roles_dengan_id_5(): void
    {
        $pembina = Role::where('name', 'pembina')->first();

        $this->assertNotNull($pembina, 'Migrasi idempoten role pembina belum jalan.');
        $this->assertSame(5, $pembina->id);
    }

    /**
     * Perbaikan Modul UKM Dinamis — Isu #2.
     * ProfileController::index() sebelumnya hanya menangani role_id 1/2/4 dan 3,
     * sehingga Pembina (5) tidak di-redirect ke mana pun (null response).
     */
    public function test_pembina_diarahkan_ke_halaman_profil_admin_saat_membuka_profil(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->get('/profil');

        $response->assertRedirect(route('admin.profil', $pembina->id));
    }

    public function test_pembina_bisa_membuka_halaman_profil_admin(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->get(route('admin.profil'));

        $response->assertStatus(200);
    }

    /**
     * Perbaikan Modul UKM Dinamis — Isu #3.
     * headnav.blade.php sebelumnya hanya merender form logout untuk
     * role_id 1/2/4, sehingga Pembina tidak melihat tombol logout sama sekali.
     * Assert pada action attribute (bukan teks "Logout") supaya tidak rapuh
     * terhadap perubahan copy di masa depan.
     */
    public function test_pembina_melihat_tombol_logout_di_dashboard_admin(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->get('/dashboard-admin');

        $response->assertStatus(200);
        $response->assertSee(route('auth.logout'), false);
    }

    public function test_pembina_bisa_logout(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);

        $response = $this->actingAs($pembina)->delete(route('auth.logout'));

        $response->assertRedirect();
        $this->assertGuest();
    }

    /**
     * Test ini hanya menguji redirect routing (authDashboard() + middleware
     * role), bukan halaman yang butuh profil lengkap (mis. kodeqr()). blok/
     * kelas/prodi di-null-kan (kolomnya nullable) supaya test tidak perlu
     * menyeed 3 tabel lookup lain hanya untuk memuaskan FK dari UserFactory.
     */
    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }
}
