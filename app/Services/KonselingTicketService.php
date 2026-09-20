<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Menerbitkan tiket SSO untuk handoff mahasiswa dari E-Management (Identity
 * Provider) ke E-Klinik (modul konseling), sesuai desain teknis di
 * docs/adr/ADR-004-integrasi-konseling-via-sso.md.
 *
 * Tiket berisi NIM mahasiswa, waktu kedaluwarsa (60 detik), dan nonce unik,
 * ditandatangani HMAC-SHA256 dengan kunci rahasia bersama. E-Klinik yang
 * memverifikasi tanda tangan, kedaluwarsa, dan keunikan nonce di sisinya —
 * service ini hanya bertanggung jawab menerbitkan tiket.
 */
class KonselingTicketService
{
    /**
     * Umur tiket dalam detik, sesuai ADR-004.
     */
    private const TICKET_TTL_SECONDS = 60;

    /**
     * Bangun URL redirect SSO lengkap (payload + tanda tangan) untuk user.
     *
     * Format query flat (nim, expires_at, nonce, signature) dan canonical
     * string tanda tangan "{nim}|{expires_at}|{nonce}" WAJIB sama persis
     * dengan yang diharapkan SsoLoginController::hasValidSignature() di sisi
     * E-Klinik (app/Http/Controllers/Auth/SsoLoginController.php pada
     * repositori E-Polbangtan_HealtCare) — kedua sisi berbagi satu kontrak.
     *
     * @throws RuntimeException jika NIM user kosong atau konfigurasi konseling belum diisi.
     */
    public function buildRedirectUrl(User $user): string
    {
        $baseUrl = config('services.konseling.url');
        $secret = config('services.konseling.secret');

        if (blank($baseUrl) || blank($secret)) {
            throw new RuntimeException(
                'Konfigurasi layanan konseling belum lengkap. Pastikan KONSELING_URL dan KONSELING_SSO_SECRET diisi di .env.'
            );
        }

        $expiresAt = now()->addSeconds(self::TICKET_TTL_SECONDS)->timestamp;
        $nonce = (string) Str::uuid();

        // 1. Mahasiswa (role_id = 3 atau default dengan NIM)
        if ($user->role_id === User::USER_ROLE_ID || ($user->role_id === null && filled($user->nim))) {
            if (blank($user->nim)) {
                throw new RuntimeException(
                    "Mahasiswa (user_id: {$user->id}) belum memiliki NIM, sehingga tiket SSO konseling tidak dapat diterbitkan."
                );
            }

            $nim = (string) $user->nim;
            $signature = $this->signV1($nim, $expiresAt, $nonce, $secret);

            return rtrim($baseUrl, '/') . '/sso?' . http_build_query([
                'nim' => $nim,
                'identifier' => $nim,
                'role' => 'mahasiswa',
                'expires_at' => $expiresAt,
                'nonce' => $nonce,
                'signature' => $signature,
            ]);
        }

        // 2. Admin (role_id = 1)
        if ($user->role_id === User::ADMIN_ROLE_ID) {
            if (blank($user->email)) {
                throw new RuntimeException(
                    "User Admin (user_id: {$user->id}) belum memiliki email, sehingga tiket SSO konseling tidak dapat diterbitkan."
                );
            }

            $identifier = (string) $user->email;
            $role = 'admin';
            $signature = $this->signV2($identifier, $role, $expiresAt, $nonce, $secret);

            $payload = [
                'identifier' => $identifier,
                'role' => $role,
                'expires_at' => $expiresAt,
                'nonce' => $nonce,
                'signature' => $signature,
            ];

            if (filled($user->name)) {
                $payload['name'] = (string) $user->name;
            }

            return rtrim($baseUrl, '/') . '/sso?' . http_build_query($payload);
        }

        // 3. Pejabat (role_id = 9)
        if ($user->role_id === User::PEJABAT_ROLE_ID) {
            if (blank($user->email)) {
                throw new RuntimeException(
                    "User Pejabat (user_id: {$user->id}) belum memiliki email, sehingga tiket SSO konseling tidak dapat diterbitkan."
                );
            }

            $identifier = (string) $user->email;
            $role = 'pejabat';
            $signature = $this->signV2($identifier, $role, $expiresAt, $nonce, $secret);

            $payload = [
                'identifier' => $identifier,
                'role' => $role,
                'expires_at' => $expiresAt,
                'nonce' => $nonce,
                'signature' => $signature,
            ];

            if (filled($user->name)) {
                $payload['name'] = (string) $user->name;
            }

            return rtrim($baseUrl, '/') . '/sso?' . http_build_query($payload);
        }

        // 4. Role lain tidak diizinkan handoff SSO ke E-Klinik
        throw new RuntimeException(
            "Role pengguna (role_id: {$user->role_id}) tidak memiliki izin akses handoff ke E-Klinik."
        );
    }

    /**
     * Hitung tanda tangan HMAC-SHA256 untuk payload v1 (Mahasiswa): "{nim}|{expires_at}|{nonce}".
     */
    private function signV1(string $nim, int $expiresAt, string $nonce, string $secret): string
    {
        $canonical = sprintf('%s|%s|%s', $nim, $expiresAt, $nonce);

        return hash_hmac('sha256', $canonical, $secret);
    }

    /**
     * Hitung tanda tangan HMAC-SHA256 untuk payload v2 (Admin & Pejabat): "{identifier}|{role}|{expires_at}|{nonce}".
     */
    private function signV2(string $identifier, string $role, int $expiresAt, string $nonce, string $secret): string
    {
        $canonical = sprintf('%s|%s|%s|%s', $identifier, $role, $expiresAt, $nonce);

        return hash_hmac('sha256', $canonical, $secret);
    }
}
