<?php

namespace Tests\Feature\Ukm;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for UkmLaporanController::pdfReport — US 1.4 (Laporan PDF Generik).
 *
 * Requirements:
 *   - Route POST admin/ukm/{ukm}/laporan/pdf.
 *   - Admin bisa mengunduh laporan UKM apa saja.
 *   - Pelatih/Pembina hanya bisa mengunduh laporan UKM tempat mereka staf aktif.
 *   - Pelatih/Pembina UKM lain DITOLAK (403) — mencegah kebocoran data lintas UKM.
 */
class UkmLaporanTest extends TestCase
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

    public function test_admin_bisa_mengunduh_laporan_ukm_mana_saja(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Laporan Admin Test', 'slug' => 'ukm-laporan-admin-test']);

        UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin',
            'jenis' => 'latihan',
            'tanggal' => '2026-08-10',
            'mulai_acara' => '16:00:00',
            'selesai_acara' => '18:00:00',
            'status_verifikasi' => 'disetujui',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ukm.laporan.pdf', $ukm->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pelatih_ukm_binaannya_bisa_mengunduh_laporan(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Laporan Pelatih Test', 'slug' => 'ukm-laporan-pelatih-test']);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $pelatih->id, 'peran' => 'pelatih', 'status' => 'aktif']);

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.laporan.pdf', $ukm->id));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_pembina_ukm_lain_ditolak_mengunduh_laporan_ukm_bukan_binaannya(): void
    {
        $pembinaUkmLain = $this->makeUser(User::PEMBINA_ROLE_ID);
        $ukmLain = Ukm::create(['nama' => 'UKM Laporan Lain', 'slug' => 'ukm-laporan-lain']);
        $ukmTarget = Ukm::create(['nama' => 'UKM Laporan Target', 'slug' => 'ukm-laporan-target']);

        UkmMember::create(['ukm_id' => $ukmLain->id, 'user_id' => $pembinaUkmLain->id, 'peran' => 'pembina', 'status' => 'aktif']);

        $response = $this->actingAs($pembinaUkmLain)->post(route('admin.ukm.laporan.pdf', $ukmTarget->id));

        $response->assertStatus(403);
    }

    public function test_pelatih_tanpa_keanggotaan_ditolak_mengunduh_laporan(): void
    {
        $pelatih = $this->makeUser(User::PELATIH_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Laporan Tanpa Anggota', 'slug' => 'ukm-laporan-tanpa-anggota']);

        $response = $this->actingAs($pelatih)->post(route('admin.ukm.laporan.pdf', $ukm->id));

        $response->assertStatus(403);
    }
}
