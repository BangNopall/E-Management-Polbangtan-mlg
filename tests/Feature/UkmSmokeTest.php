<?php

namespace Tests\Feature;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\User;
use Tests\TestCase;

/**
 * Smoke Tests Modul UKM Dinamis (Epic 01).
 *
 * Menguji ketermuatan halaman-halaman utama modul UKM Dinamis tanpa HTTP 500.
 * Mengikuti pola tests/Feature/Laravel13SmokeTest.php (tanpa RefreshDatabase).
 */
class UkmSmokeTest extends TestCase
{
    public function test_halaman_kelola_ukm_admin_bisa_dimuat(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        Ukm::updateOrCreate(['slug' => 'ukm-smoke-test'], ['nama' => 'UKM Smoke Test', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/ukm');

        $response->assertStatus(200);
    }

    public function test_halaman_ukm_saya_mahasiswa_bisa_dimuat(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard/ukm');

        $response->assertStatus(200);
    }

    public function test_halaman_riwayat_ukm_mahasiswa_bisa_dimuat(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard/ukm/riwayat');

        $response->assertStatus(200);
    }
}
