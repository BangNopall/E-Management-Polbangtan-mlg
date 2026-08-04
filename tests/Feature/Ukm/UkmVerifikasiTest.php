<?php

namespace Tests\Feature\Ukm;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Milestone E dari .claude/plans/epic-01-ukm-dinamis.md — US 1.4 (Verifikasi Pembina & Laporan PDF).
 *
 * Requirements:
 *   - Route GET admin/ukm/{ukm}/verifikasi (index) & PATCH admin/ukm/jadwal/{jadwal}/verifikasi (update).
 *   - Akses di-gate role:admin,pembina.
 *   - Pembina hanya bisa verifikasi UKM tempat dia terdaftar sebagai 'pembina'. Pembina UKM lain DITOLAK (403).
 *   - Admin (role_id=1) bisa verifikasi UKM apa saja.
 *   - Transisi status: draft -> menunggu -> disetujui / ditolak.
 *   - Update status mencatat verified_by, verified_at, dan catatan_pembina.
 */
class UkmVerifikasiTest extends TestCase
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

    public function test_pembina_bisa_melihat_daftar_verifikasi_ukm_binaannya(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Seni Musik', 'slug' => 'ukm-seni-musik']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $pembina->id,
            'peran' => 'pembina',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($pembina)->get(route('admin.ukm.verifikasi.index', $ukm->id));
        $response->assertStatus(200);
        $response->assertViewIs('admin.ukm.verifikasi');
        $response->assertSee('UKM Seni Musik');
    }

    public function test_pembina_ukm_lain_ditolak_mengakses_verifikasi_ukm_bukan_binaannya(): void
    {
        $pembinaUKMA = $this->makeUser(User::PEMBINA_ROLE_ID);
        $pembinaUKMB = $this->makeUser(User::PEMBINA_ROLE_ID);

        $ukmA = Ukm::create(['nama' => 'UKM A', 'slug' => 'ukm-a']);
        $ukmB = Ukm::create(['nama' => 'UKM B', 'slug' => 'ukm-b']);

        UkmMember::create(['ukm_id' => $ukmA->id, 'user_id' => $pembinaUKMA->id, 'peran' => 'pembina', 'status' => 'aktif']);
        UkmMember::create(['ukm_id' => $ukmB->id, 'user_id' => $pembinaUKMB->id, 'peran' => 'pembina', 'status' => 'aktif']);

        // Pembina A tries to access verification for UKM B
        $response = $this->actingAs($pembinaUKMA)->get(route('admin.ukm.verifikasi.index', $ukmB->id));
        $response->assertStatus(403);
    }

    public function test_pembina_bisa_menyetujui_jadwal_kegiatan_menunggu(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Tari', 'slug' => 'ukm-tari']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Pentas Seni',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => '2026-08-20',
            'mulai_acara' => '19:00:00',
            'selesai_acara' => '21:00:00',
            'status_verifikasi' => 'menunggu',
        ]);

        $response = $this->actingAs($pembina)->patch(route('admin.ukm.verifikasi.update', $jadwal->id), [
            'status_verifikasi' => 'disetujui',
            'catatan_pembina' => 'Laporan lengkap, disetujui.',
        ]);

        $response->assertRedirect(route('admin.ukm.verifikasi.index', $ukm->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ukm_jadwals', [
            'id' => $jadwal->id,
            'status_verifikasi' => 'disetujui',
            'verified_by' => $pembina->id,
            'catatan_pembina' => 'Laporan lengkap, disetujui.',
        ]);
    }

    public function test_pembina_bisa_menolak_jadwal_kegiatan(): void
    {
        $pembina = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Catur', 'slug' => 'ukm-catur']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pembina->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Turnamen Internal',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-22',
            'mulai_acara' => '09:00:00',
            'selesai_acara' => '12:00:00',
            'status_verifikasi' => 'menunggu',
        ]);

        $response = $this->actingAs($pembina)->patch(route('admin.ukm.verifikasi.update', $jadwal->id), [
            'status_verifikasi' => 'ditolak',
            'catatan_pembina' => 'Ruangan belum dikonfirmasi.',
        ]);

        $response->assertRedirect(route('admin.ukm.verifikasi.index', $ukm->id));
        $this->assertDatabaseHas('ukm_jadwals', [
            'id' => $jadwal->id,
            'status_verifikasi' => 'ditolak',
            'verified_by' => $pembina->id,
            'catatan_pembina' => 'Ruangan belum dikonfirmasi.',
        ]);
    }

    public function test_admin_bisa_memverifikasi_jadwal_ukm_mana_saja(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Futsal', 'slug' => 'ukm-futsal']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Sparring',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-25',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'status_verifikasi' => 'menunggu',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.ukm.verifikasi.update', $jadwal->id), [
            'status_verifikasi' => 'disetujui',
        ]);

        $response->assertRedirect(route('admin.ukm.verifikasi.index', $ukm->id));
        $this->assertDatabaseHas('ukm_jadwals', [
            'id' => $jadwal->id,
            'status_verifikasi' => 'disetujui',
            'verified_by' => $admin->id,
        ]);
    }
}
