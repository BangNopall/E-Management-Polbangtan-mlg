<?php

namespace App\Http\Controllers;

use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UkmLaporanController extends Controller
{
    /**
     * Generate PDF report for a generic UKM by ukm_id.
     * Uses single generic template admin/generate/generate-ukm.blade.php.
     */
    public function pdfReport(Request $request, Ukm $ukm): Response
    {
        $user = Auth::user();
        if ($user->role_id !== User::ADMIN_ROLE_ID) {
            $isStaffOfThisUkm = UkmMember::where('ukm_id', $ukm->id)
                ->where('user_id', $user->id)
                ->whereIn('peran', ['pelatih', 'pembina'])
                ->where('status', 'aktif')
                ->exists();

            abort_unless($isStaffOfThisUkm, 403, 'Anda tidak berhak mengunduh laporan UKM ini.');
        }

        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
        ]);

        $query = $ukm->jadwals()->with(['presensis.user', 'verifier']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('tanggal', [$request->input('tanggal_mulai'), $request->input('tanggal_selesai')]);
        }

        $jadwals = $query->latest('tanggal')->get();

        $pdf = Pdf::loadView('admin.generate.generate-ukm', [
            'ukm' => $ukm,
            'jadwals' => $jadwals,
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
        ]);

        $filename = 'Laporan-UKM-' . $ukm->slug . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
