<?php

namespace App\Jobs;

use App\Models\PengajuanIzin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateSuratIzinPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $pengajuan;

    /**
     * Create a new job instance.
     */
    public function __construct(PengajuanIzin $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pengajuan = $this->pengajuan;
        $pengajuan->load(['user', 'jenisIzin', 'ukm', 'approvals.approver']);

        $signedUrl = URL::signedRoute('publik.verifikasi.izin', ['qr_token' => $pengajuan->qr_token]);

        $qrBase64 = null;
        try {
            $pngQr = QrCode::format('png')->size(1000)->margin(1)->generate($signedUrl);
            $qrBase64 = 'data:image/png;base64,'.base64_encode($pngQr);
        } catch (\Throwable $e) {
            $qrBase64 = null;
        }

        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'chroot' => public_path(),
        ])->loadView('admin.generate.generate-izin', [
            'pengajuan' => $pengajuan,
            'signedUrl' => $signedUrl,
            'qrBase64' => $qrBase64,
        ]);

        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Surat_Izin_'.str_replace('/', '_', $pengajuan->nomor_surat ?? 'DRAFT').'.pdf';
        
        Storage::disk('public')->put('izins/'.$fileName, $pdf->output());
    }
}
