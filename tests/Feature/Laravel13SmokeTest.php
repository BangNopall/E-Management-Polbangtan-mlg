<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Laravel 13 Compatibility Smoke Tests
 *
 * Verifies the four core feature areas load without 500/error after the
 * Laravel 12 → 13 upgrade. Uses existing database data — no RefreshDatabase —
 * so it is safe to run against the development database.
 *
 * Milestone 4 of .claude/prds/laravel13-compatibility-audit.prd.md
 */
class Laravel13SmokeTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Auth & Role System
    // -------------------------------------------------------------------------

    public function test_login_page_loads(): void
    {
        $response = $this->get('/');

        // Unauthenticated root redirects to login — correct behaviour.
        $response->assertStatus(302);
    }

    public function test_admin_dashboard_loads_when_authenticated(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($admin)->get('/dashboard-admin');

        $response->assertStatus(200);
    }

    public function test_student_dashboard_loads_when_authenticated(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertStatus(200);
    }

    public function test_role_middleware_blocks_student_from_admin_dashboard(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard-admin');

        // EnsureUserHasRole redirects on failure — not 403.
        $response->assertStatus(302);
    }

    // -------------------------------------------------------------------------
    // QR Code Scanning
    // -------------------------------------------------------------------------

    public function test_student_qr_code_page_loads(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard/kode-qr');

        $response->assertStatus(200);
    }

    public function test_qr_hukum_page_loads_for_student(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard/qr-hukum');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Absensi (Attendance)
    // -------------------------------------------------------------------------

    public function test_admin_absensi_page_loads(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($admin)->get('/data-absen-keluar');

        $response->assertStatus(200);
    }

    public function test_student_riwayat_absen_loads(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($student)->get('/dashboard/riwayat-absen');

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // PDF & Excel Export (barryvdh/laravel-dompdf + maatwebsite/excel)
    // -------------------------------------------------------------------------

    public function test_generate_report_page_loads_for_admin(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($admin)->get('/generate-laporan');

        // Acceptable: 200 (page loads) or 302 (redirect to sub-page).
        $this->assertContains($response->status(), [200, 302],
            "Expected 200 or 302 for generate-laporan, got {$response->status()}");
    }
}
