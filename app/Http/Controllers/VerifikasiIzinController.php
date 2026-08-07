<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use Illuminate\Http\Request;

class VerifikasiIzinController extends Controller
{
    /**
     * Halaman Verifikasi Publik Keabsahan Surat Perizinan Asrama.
     * Dilindungi middleware 'signed' bawaan Laravel (ADR-008).
     */
    public function show(Request $request, string $qr_token)
    {
        $pengajuan = PengajuanIzin::with(['user', 'jenisIzin', 'ukm', 'approvals.approver'])
            ->where('qr_token', $qr_token)
            ->firstOrFail();

        return view('publik.verifikasi-izin', compact('pengajuan'));
    }
}
