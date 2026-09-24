<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Feature test untuk route handoff SSO konseling (GET /handoff/konseling).
 * Lihat docs/adr/ADR-004-integrasi-konseling-via-sso.md.
 *
 * Mengikuti pola Laravel13SmokeTest: mengambil user existing dari database
 * dev lewat User::where(...)->firstOrFail() — tidak memakai factory atau
 * RefreshDatabase, karena phpunit.xml tidak mengonfigurasi koneksi test
 * terpisah (lihat catatan di phpunit.xml).
 */
class KonselingHandoffTest extends TestCase
{
    private const TEST_URL = 'https://eklinik.test';
    private const TEST_SECRET = 'test-shared-secret';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.konseling.url', self::TEST_URL);
        Config::set('services.konseling.secret', self::TEST_SECRET);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/handoff/konseling');

        $response->assertRedirect(route('auth.login'));
    }

    public function test_student_is_redirected_to_eklinik_sso_endpoint_with_valid_signature(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)
            ->whereNotNull('nim')
            ->firstOrFail();

        $response = $this->actingAs($student)->get('/handoff/konseling');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(self::TEST_URL . '/sso?', $location);

        $query = [];
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame($student->nim, $query['nim']);
        $this->assertSame('mahasiswa', $query['role']);

        $canonical = sprintf('%s|%s|%s', $query['nim'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);
        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_admin_is_redirected_to_eklinik_sso_endpoint_with_valid_signature(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($admin)->get('/handoff/konseling');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(self::TEST_URL . '/sso?', $location);

        $query = [];
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame($admin->email, $query['identifier']);
        $this->assertSame('admin', $query['role']);

        $canonical = sprintf('%s|%s|%s|%s', $query['identifier'], $query['role'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);
        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_pejabat_is_redirected_to_eklinik_sso_endpoint_with_valid_signature(): void
    {
        $pejabat = User::where('role_id', User::PEJABAT_ROLE_ID)->first() ?? User::create([
            'name' => 'Bapak Kaprodi Test',
            'email' => 'kaprodi_test@polbangtanmalang.ac.id',
            'role_id' => User::PEJABAT_ROLE_ID,
            'password' => bcrypt('password'),
        ]);
        $pejabat->update(['is_password_changed' => true]);

        $response = $this->actingAs($pejabat)->get('/handoff/konseling');

        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertStringStartsWith(self::TEST_URL . '/sso?', $location);

        $query = [];
        parse_str(parse_url($location, PHP_URL_QUERY), $query);

        $this->assertSame($pejabat->email, $query['identifier']);
        $this->assertSame('pejabat', $query['role']);
        $this->assertSame($pejabat->name, $query['name']);

        $canonical = sprintf('%s|%s|%s|%s', $query['identifier'], $query['role'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);
        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_unauthorized_role_is_blocked_by_role_middleware(): void
    {
        $operator = User::where('role_id', User::OPERATOR_ROLE_ID)->first() ?? User::create([
            'name' => 'Operator Test',
            'email' => 'operator_test@polbangtanmalang.ac.id',
            'role_id' => User::OPERATOR_ROLE_ID,
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($operator)->get('/handoff/konseling');

        // EnsureUserHasRole redirects on failure — not 403 (see AGENTS/CLAUDE.md).
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.index'));
    }
}
