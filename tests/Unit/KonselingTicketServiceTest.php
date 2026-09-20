<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\KonselingTicketService;
use Illuminate\Support\Facades\Config;
use RuntimeException;
use Tests\TestCase;

/**
 * Unit test murni untuk KonselingTicketService — tidak menyentuh HTTP layer
 * atau database. Lihat docs/adr/ADR-004-integrasi-konseling-via-sso.md.
 */
class KonselingTicketServiceTest extends TestCase
{
    private const TEST_URL = 'https://eklinik.test';
    private const TEST_SECRET = 'test-shared-secret';

    private function service(): KonselingTicketService
    {
        Config::set('services.konseling.url', self::TEST_URL);
        Config::set('services.konseling.secret', self::TEST_SECRET);

        return new KonselingTicketService();
    }

    /**
     * Parse query string flat (nim, expires_at, nonce, signature) — format
     * ini harus sama persis dengan yang dibaca SsoLoginController di E-Klinik.
     */
    private function decodeRedirectUrl(string $url): array
    {
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY), $query);

        return $query;
    }

    public function test_redirect_url_points_to_configured_base_url_and_sso_path(): void
    {
        $user = new User(['id' => 1, 'nim' => '2201012345']);

        $url = $this->service()->buildRedirectUrl($user);

        $this->assertStringStartsWith(self::TEST_URL . '/sso?', $url);
    }

    public function test_query_contains_nim_expiry_and_nonce(): void
    {
        $user = new User(['id' => 1, 'nim' => '2201012345']);

        $query = $this->decodeRedirectUrl($this->service()->buildRedirectUrl($user));

        $this->assertSame('2201012345', $query['nim']);
        $this->assertArrayHasKey('expires_at', $query);
        $this->assertArrayHasKey('nonce', $query);
        // Kedaluwarsa harus sekitar 60 detik dari sekarang (ADR-004).
        $this->assertEqualsWithDelta(now()->addSeconds(60)->timestamp, (int) $query['expires_at'], 2);
    }

    public function test_signature_is_valid_hmac_sha256_over_canonical_string(): void
    {
        $query = $this->decodeRedirectUrl(
            $this->service()->buildRedirectUrl(new User(['id' => 1, 'nim' => '2201012345']))
        );

        $canonical = sprintf('%s|%s|%s', $query['nim'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);

        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_each_call_produces_a_different_nonce(): void
    {
        $service = $this->service();
        $user = new User(['id' => 1, 'nim' => '2201012345']);

        $first = $this->decodeRedirectUrl($service->buildRedirectUrl($user));
        $second = $this->decodeRedirectUrl($service->buildRedirectUrl($user));

        $this->assertNotSame($first['nonce'], $second['nonce']);
    }

    public function test_throws_when_user_nim_is_blank(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service()->buildRedirectUrl(new User(['id' => 1, 'nim' => null]));
    }

    public function test_throws_when_konseling_config_is_missing(): void
    {
        Config::set('services.konseling.url', null);
        Config::set('services.konseling.secret', null);

        $this->expectException(RuntimeException::class);

        (new KonselingTicketService())->buildRedirectUrl(new User(['id' => 1, 'nim' => '2201012345']));
    }

    public function test_admin_user_generates_signed_ticket_with_email_and_admin_role(): void
    {
        $admin = new User([
            'id' => 10,
            'name' => 'Administrator Utama',
            'email' => 'admin@polbangtanmalang.ac.id',
            'role_id' => User::ADMIN_ROLE_ID,
        ]);

        $query = $this->decodeRedirectUrl($this->service()->buildRedirectUrl($admin));

        $this->assertSame('admin@polbangtanmalang.ac.id', $query['identifier']);
        $this->assertSame('admin', $query['role']);
        $this->assertSame('Administrator Utama', $query['name']);
        $this->assertArrayHasKey('expires_at', $query);
        $this->assertArrayHasKey('nonce', $query);

        $canonical = sprintf('%s|%s|%s|%s', $query['identifier'], $query['role'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);
        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_pejabat_user_generates_signed_ticket_with_email_name_and_pejabat_role(): void
    {
        $pejabat = new User([
            'id' => 20,
            'name' => 'Bapak Kaprodi',
            'email' => 'kaprodi@polbangtanmalang.ac.id',
            'role_id' => User::PEJABAT_ROLE_ID,
        ]);

        $query = $this->decodeRedirectUrl($this->service()->buildRedirectUrl($pejabat));

        $this->assertSame('kaprodi@polbangtanmalang.ac.id', $query['identifier']);
        $this->assertSame('pejabat', $query['role']);
        $this->assertSame('Bapak Kaprodi', $query['name']);
        $this->assertArrayHasKey('expires_at', $query);
        $this->assertArrayHasKey('nonce', $query);

        $canonical = sprintf('%s|%s|%s|%s', $query['identifier'], $query['role'], $query['expires_at'], $query['nonce']);
        $expectedSignature = hash_hmac('sha256', $canonical, self::TEST_SECRET);
        $this->assertTrue(hash_equals($expectedSignature, $query['signature']));
    }

    public function test_throws_when_admin_email_is_blank(): void
    {
        $this->expectException(RuntimeException::class);

        $admin = new User([
            'id' => 10,
            'email' => null,
            'role_id' => User::ADMIN_ROLE_ID,
        ]);

        $this->service()->buildRedirectUrl($admin);
    }

    public function test_throws_when_unauthorized_role_attempts_sso(): void
    {
        $this->expectException(RuntimeException::class);

        $operator = new User([
            'id' => 15,
            'email' => 'operator@polbangtanmalang.ac.id',
            'role_id' => User::OPERATOR_ROLE_ID,
        ]);

        $this->service()->buildRedirectUrl($operator);
    }
}
