<?php

namespace App\Http\Controllers;

use App\Exports\LaporanIzinExport;
use App\Models\PengajuanIzin;
use App\Models\Prodi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AdminIzinDataController extends Controller
{
    /**
     * Tampilkan seluruh data perizinan mahasiswa dengan filter & pencarian.
     */
    public function index(Request $request)
    {
        $prodis = Prodi::all();
        $izins = $this->buildQuery($request)->paginate(20);

        return view('admin.izin.data.index', compact('izins', 'prodis'));
    }

    /**
     * Tampilkan detail data perizinan lengkap beserta audit trail terstruktur.
     */
    public function show(PengajuanIzin $pengajuan)
    {
        $pengajuan->load([
            'user',
            'jenisIzin',
            'ukm',
            'pelanggaran',
            'approvals.approver',
            'approvals.aktor',
        ]);

        return view('admin.izin.data.show', compact('pengajuan'));
    }

    /**
     * Ekspor Laporan Rekapitulasi Perizinan berformat PDF.
     */
    public function exportPdf(Request $request)
    {
        $izins = $this->buildQuery($request)->get();

        $pdf = Pdf::loadView('admin.generate.generate-laporan-izin', [
            'izins' => $izins,
            'tanggalMulai' => $request->input('tanggal_mulai'),
            'tanggalSelesai' => $request->input('tanggal_selesai'),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $filename = 'Laporan-Perizinan-Asrama-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Ekspor Laporan Rekapitulasi Perizinan berformat Excel (XLSX via FromView).
     */
    public function exportExcel(Request $request)
    {
        $izins = $this->buildQuery($request)->get();

        $export = new LaporanIzinExport(
            $izins,
            $request->input('tanggal_mulai'),
            $request->input('tanggal_selesai')
        );

        $filename = 'Laporan-Perizinan-Asrama-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download($export, $filename);
    }

    /**
     * Helper query builder untuk data perizinan.
     */
    protected function buildQuery(Request $request)
    {
        $query = PengajuanIzin::with(['user', 'jenisIzin', 'ukm', 'approvals']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('prodi_id')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('prodi_id', $request->prodi_id);
            });
        }

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $query->whereBetween('waktu_berangkat', [
                $request->tanggal_mulai . ' 00:00:00',
                $request->tanggal_selesai . ' 23:59:59',
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_snapshot', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%")
                  ->orWhere('tujuan_lokasi', 'like', "%{$search}%");
            });
        }

        return $query->latest('created_at');
    }
}
