<?php

namespace App\Http\Controllers;

use App\Models\IzinApproval;
use App\Models\PengajuanIzin;
use App\Models\User;
use App\Services\Izin\ApproverResolver;
use App\Services\Izin\PengajuanIzinService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = auth()->user();
        $userId = $user->id;

        $query = IzinApproval::with([
                'pengajuan.user',
                'pengajuan.jenisIzin',
                'pengajuan.ukm'
            ])
            ->where('status', 'menunggu')
            ->whereHas('pengajuan', function ($q) {
                $q->whereColumn('pengajuan_izins.langkah_aktif', 'izin_approvals.urutan')
                  ->whereIn('pengajuan_izins.status', ['diajukan', 'menunggu']);
            });

        if ($user->role_id === User::OPERATOR_ROLE_ID) {
            $query->where(function ($q) use ($userId) {
                $q->where('approver_user_id', $userId)
                  ->orWhere(function ($q2) use ($userId) {
                      $q2->whereHas('pengajuan.jenisIzin.steps', function ($stepQuery) {
                          $stepQuery->whereColumn('izin_workflow_steps.urutan', 'izin_approvals.urutan')
                                    ->where('izin_workflow_steps.resolver', 'petugas_jaga');
                      })
                      ->where(function ($q3) use ($userId) {
                          // Kasus 1: Dijadwalkan piket pada tanggal keberangkatan
                          $q3->whereHas('pengajuan', function ($pengajuanQuery) use ($userId) {
                              $pengajuanQuery->whereExists(function ($sub) use ($userId) {
                                  $sub->select(DB::raw(1))
                                      ->from('jadwal_petugas')
                                      ->whereColumn('jadwal_petugas.date', DB::raw('DATE(pengajuan_izins.waktu_berangkat)'))
                                      ->where(function ($petugasQuery) use ($userId) {
                                          $petugasQuery->where('petugas1_id', $userId)
                                                       ->orWhere('petugas2_id', $userId);
                                      });
                              });
                          })
                          // Kasus 2: Fallback (jadwal belum dibuat atau kedua slot petugas kosong pada tanggal keberangkatan)
                          ->orWhereHas('pengajuan', function ($pengajuanQuery) {
                              $pengajuanQuery->whereNotExists(function ($sub) {
                                  $sub->select(DB::raw(1))
                                      ->from('jadwal_petugas')
                                      ->whereColumn('jadwal_petugas.date', DB::raw('DATE(pengajuan_izins.waktu_berangkat)'))
                                      ->where(function ($petugasQuery) {
                                          $petugasQuery->whereNotNull('petugas1_id')
                                                       ->orWhereNotNull('petugas2_id');
                                      });
                              });
                          });
                      });
                  });
            });
        } else {
            $query->where('approver_user_id', $userId);
        }

        $approvals = $query->latest('dibuka_at')
            ->paginate(20)->withQueryString();

        return view('admin.izin.inbox', compact('approvals'));
    }

    /**
     * Cari approval aktif untuk pengajuan ini dan verifikasi apakah user berhak memprosesnya.
     */
    protected function getAuthorizedApproval(PengajuanIzin $pengajuan, User $user): ?IzinApproval
    {
        $approval = IzinApproval::where('pengajuan_izin_id', $pengajuan->id)
            ->where('urutan', $pengajuan->langkah_aktif)
            ->first();

        if (!$approval) {
            return null;
        }

        if ($approval->approver_user_id === $user->id) {
            return $approval;
        }

        // Jika user bukan approver_user_id spesifik, cek apakah user adalah kandidat sah pada langkah ini
        $step = $pengajuan->jenisIzin->steps()->where('urutan', $approval->urutan)->first();
        if ($step) {
            $context = [
                'user' => $pengajuan->user,
                'ukm_id' => $pengajuan->ukm_id,
                'waktu_berangkat' => optional($pengajuan->waktu_berangkat)->format('Y-m-d'),
            ];

            $res = app(ApproverResolver::class)->resolve($step, $context);
            if ($res['candidates']->pluck('id')->contains($user->id)) {
                return $approval;
            }
        }

        return null;
    }

    /**
     * Tampilkan detail perizinan untuk ditinjau oleh approver.
     * Memeriksa 3 abort pengaman §2.3 SystemFlow.
     */
    public function review(PengajuanIzin $pengajuan)
    {
        $user = auth()->user();
        $approval = $this->getAuthorizedApproval($pengajuan, $user);

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

        $user = auth()->user();
        $approval = $this->getAuthorizedApproval($pengajuan, $user);

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

        $catatan = $request->input('catatan');

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

        $fileName = 'Surat_Izin_'.str_replace('/', '_', $pengajuan->nomor_surat ?? 'DRAFT').'.pdf';
        
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('izins/'.$fileName)) {
            return response()->file(storage_path('app/public/izins/'.$fileName));
        }

        return back()->with('info', 'File Surat Izin sedang diproses oleh sistem (Background Job). Silakan tunggu beberapa saat dan coba lagi.');
    }
}
