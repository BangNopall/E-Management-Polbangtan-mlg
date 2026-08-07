<?php

namespace App\Http\Controllers;

use App\Models\IzinApproval;
use App\Models\PengajuanIzin;
use App\Services\Izin\PengajuanIzinService;
use Illuminate\Http\Request;

class IzinPersetujuanController extends Controller
{
    protected PengajuanIzinService $pengajuanService;

    public function __construct(PengajuanIzinService $pengajuanService)
    {
        $this->pengajuanService = $pengajuanService;
    }

    /**
     * Tampilkan inbox persetujuan izin untuk approver yang sedang login.
     * Hanya menampilkan persetujuan di mana user adalah approver berhak
     * DAN langkah persetujuan sedang aktif (langkah_aktif = urutan).
     */
    public function inbox(Request $request)
    {
        $userId = auth()->id();

        $approvals = IzinApproval::with([
                'pengajuan.user',
                'pengajuan.jenisIzin',
                'pengajuan.ukm'
            ])
            ->where('approver_user_id', $userId)
            ->where('status', 'menunggu')
            ->whereHas('pengajuan', function ($q) {
                $q->whereColumn('pengajuan_izins.langkah_aktif', 'izin_approvals.urutan')
                  ->whereIn('pengajuan_izins.status', ['diajukan', 'menunggu']);
            })
            ->latest('dibuka_at')
            ->paginate(10);

        return view('admin.izin.inbox', compact('approvals'));
    }

    /**
     * Tampilkan detail perizinan untuk ditinjau oleh approver.
     * Memeriksa 3 abort pengaman §2.3 SystemFlow.
     */
    public function review(PengajuanIzin $pengajuan)
    {
        $userId = auth()->id();

        $approval = IzinApproval::where('pengajuan_izin_id', $pengajuan->id)
            ->where('approver_user_id', $userId)
            ->first();

        // Security Abort 1: User bukan penandatangan yang berhak
        abort_unless($approval, 403, 'Anda bukan penandatangan yang berhak untuk langkah perizinan ini.');

        // Security Abort 2: Langkah sebelumnya belum selesai diproses
        abort_unless(
            (int)$pengajuan->langkah_aktif === (int)$approval->urutan,
            409,
            'Langkah persetujuan sebelumnya belum selesai diproses.'
        );

        // Security Abort 3: Langkah ini sudah diputuskan sebelumnya
        abort_unless(
            $approval->status === 'menunggu',
            409,
            'Langkah persetujuan ini sudah diputuskan sebelumnya.'
        );

        $pengajuan->load(['user', 'jenisIzin', 'ukm', 'approvals.approver']);

        return view('admin.izin.review', compact('pengajuan', 'approval'));
    }

    /**
     * Memproses keputusan (Setujui atau Tolak) atas pengajuan izin.
     */
    public function putuskan(Request $request, PengajuanIzin $pengajuan)
    {
        $request->validate([
            'keputusan' => ['required', 'string', 'in:setujui,tolak'],
            'catatan' => ['nullable', 'string', 'max:1000', 'required_if:keputusan,tolak'],
        ], [
            'keputusan.required' => 'Pilih keputusan (Setujui atau Tolak).',
            'catatan.required_if' => 'Alasan penolakan wajib diisi ketika Anda menolak pengajuan izin.',
        ]);

        $userId = auth()->id();

        $approval = IzinApproval::where('pengajuan_izin_id', $pengajuan->id)
            ->where('approver_user_id', $userId)
            ->first();

        // 3 Security Aborts (§2.3 SystemFlow)
        abort_unless($approval, 403, 'Anda bukan penandatangan yang berhak untuk langkah perizinan ini.');
        abort_unless(
            (int)$pengajuan->langkah_aktif === (int)$approval->urutan,
            409,
            'Langkah persetujuan sebelumnya belum selesai diproses.'
        );
        abort_unless(
            $approval->status === 'menunggu',
            409,
            'Langkah persetujuan ini sudah diputuskan.'
        );

        $user = auth()->user();
        $catatan = $request->input('catatan');
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        if ($request->input('keputusan') === 'setujui') {
            $this->pengajuanService->setujui($approval, $user, $catatan);
            $message = 'Pengajuan izin berhasil disetujui.';
        } else {
            $this->pengajuanService->tolak($approval, $user, $catatan);
            $message = 'Pengajuan izin telah ditolak.';
        }

        return redirect()->route('admin.izin.persetujuan.inbox')->with('success', $message);
    }

    /**
     * Download PDF Surat Izin Resmi untuk approver/staf.
     */
    public function downloadPdf(PengajuanIzin $pengajuan)
    {
        $userId = auth()->id();
        $isApprover = $pengajuan->approvals()->where('approver_user_id', $userId)->exists();
        $isStaffRole = in_array(auth()->user()->role_id, [1, 2, 4, 5]);

        abort_unless($isApprover || $isStaffRole, 403, 'Anda tidak berhak mengunduh dokumen perizinan ini.');
        abort_unless(in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']), 403, 'Surat izin belum disetujui.');

        $pdfService = app(\App\Services\Izin\SuratIzinPdfService::class);
        return $pdfService->generate($pengajuan);
    }
}
