<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IzinMonitorController extends Controller
{
    /**
     * Tampilkan halaman Monitor Asrama (Staff Dashboard Real-Time).
     */
    public function index(Request $request)
    {
        $stats = $this->getStatsData();
        $izins = $this->getMonitorQuery($request)->paginate(15);

        return view('admin.izin.monitor', compact('stats', 'izins'));
    }

    /**
     * Endpoint JSON untuk auto-refresh 60 detik via fetch API biasa.
     */
    public function data(Request $request)
    {
        $stats = $this->getStatsData();
        $izins = $this->getMonitorQuery($request)->get();

        $formatted = $izins->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->nama_snapshot,
                'nirm' => $item->nirm_snapshot ?? '-',
                'prodi' => $item->prodi_snapshot,
                'kelas' => $item->kelas_snapshot,
                'jenis_izin' => optional($item->jenisIzin)->nama ?? '-',
                'tujuan' => $item->tujuan_lokasi,
                'waktu_berangkat' => optional($item->waktu_berangkat)->format('d/m/Y H:i'),
                'waktu_kembali' => optional($item->waktu_kembali)->format('d/m/Y H:i'),
                'status' => strtoupper($item->status),
                'status_raw' => $item->status,
                'tiba_at' => $item->tiba_at ? $item->tiba_at->format('d/m/Y H:i') : null,
                'tiba_oleh' => $item->tiba_dikonfirmasi_oleh,
                'review_url' => route('admin.izin.persetujuan.review', $item->id),
            ];
        });

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'data' => $formatted,
            'last_updated' => Carbon::now()->format('H:i:s'),
        ]);
    }

    /**
     * Hitung agregasi 3 kartu statistik ringkas.
     */
    private function getStatsData(): array
    {
        $now = Carbon::now();

        return [
            'sedang_berjalan' => PengajuanIzin::where('status', 'berjalan')->count(),
            'terlambat' => PengajuanIzin::where('status', 'terlambat')->count(),
            'mendatang' => PengajuanIzin::where('status', 'disetujui')
                ->where('waktu_berangkat', '>', $now)
                ->count(),
        ];
    }

    /**
     * Query builder daftar perizinan aktif / berjalan / terlambat.
     */
    private function getMonitorQuery(Request $request)
    {
        $query = PengajuanIzin::with(['user', 'jenisIzin', 'ukm'])
            ->whereIn('status', ['disetujui', 'berjalan', 'terlambat']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_snapshot', 'like', "%{$search}%")
                  ->orWhere('tujuan_lokasi', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        return $query->latest('waktu_berangkat');
    }
}
