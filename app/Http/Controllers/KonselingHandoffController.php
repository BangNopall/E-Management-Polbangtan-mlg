<?php

namespace App\Http\Controllers;

use App\Services\KonselingTicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Menerbitkan tiket SSO dan mengarahkan mahasiswa ke E-Klinik untuk layanan
 * konseling. Lihat docs/adr/ADR-004-integrasi-konseling-via-sso.md.
 */
class KonselingHandoffController extends Controller
{
    public function __construct(private readonly KonselingTicketService $tickets)
    {
    }

    public function redirect(): RedirectResponse
    {
        try {
            $url = $this->tickets->buildRedirectUrl(Auth::user());
        } catch (RuntimeException $e) {
            Log::error('Gagal menerbitkan tiket SSO konseling: ' . $e->getMessage());

            return back()->with('error', 'Layanan Konseling sedang tidak tersedia, silakan coba lagi nanti.');
        }

        return redirect()->away($url);
    }
}
