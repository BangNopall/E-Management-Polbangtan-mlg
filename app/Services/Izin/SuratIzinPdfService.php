<?php

namespace App\Services\Izin;

use App\Models\PengajuanIzin;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SuratIzinPdfService
{
    /**
     * Generate & stream PDF Surat Izin Resmi untuk pengajuan izin yang disetujui.
     */
    public function generate(PengajuanIzin $pengajuan)
    {
        $pengajuan->load(['user', 'jenisIzin', 'ukm', 'approvals.approver']);

        // Generate URL Verifikasi Publik dengan Laravel Signed URL (ADR-008)
        $signedUrl = URL::signedRoute('publik.verifikasi.izin', ['qr_token' => $pengajuan->qr_token]);

        // Generate QR Code base64 image (PNG)
        $qrBase64 = null;
        try {
            $pngQr = QrCode::format('png')->size(1000)->margin(1)->generate($signedUrl);
            $qrBase64 = 'data:image/png;base64,'.base64_encode($pngQr);
        } catch (\Throwable $e) {
            // Fallback jika ekstensi GD / BaconQrCode mengalami kendala
            $qrBase64 = null;
        }

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(), // Mengizinkan DomPDF membaca folder public
        ])->loadView('admin.generate.generate-izin', [
            'pengajuan' => $pengajuan,
            'signedUrl' => $signedUrl,
            'qrBase64' => $qrBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Surat_Izin_'.str_replace('/', '_', $pengajuan->nomor_surat ?? 'DRAFT').'.pdf';

        return $pdf->stream($fileName);
    }
}
