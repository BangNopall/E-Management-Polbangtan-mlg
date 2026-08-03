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

        if (blank($user->nim)) {
            throw new RuntimeException(
                "Mahasiswa (user_id: {$user->id}) belum memiliki NIM, sehingga tiket SSO konseling tidak dapat diterbitkan."
            );
        }

        $nim = $user->nim;
        $expiresAt = now()->addSeconds(self::TICKET_TTL_SECONDS)->timestamp;
        $nonce = (string) Str::uuid();
        $signature = $this->sign($nim, $expiresAt, $nonce, $secret);

        return rtrim($baseUrl, '/') . '/sso?' . http_build_query([
            'nim' => $nim,
            'expires_at' => $expiresAt,
            'nonce' => $nonce,
            'signature' => $signature,
        ]);
    }

    /**
     * Hitung tanda tangan HMAC-SHA256 atas canonical string "{nim}|{expires_at}|{nonce}".
     *
     * Urutan dan separator "|" tetap dan tidak boleh diubah tanpa mengubah
     * juga SsoLoginController::hasValidSignature() di E-Klinik.
     */
    private function sign(string $nim, int $expiresAt, string $nonce, string $secret): string
    {
        $canonical = sprintf('%s|%s|%s', $nim, $expiresAt, $nonce);

        return hash_hmac('sha256', $canonical, $secret);
    }
}
