<?php

namespace App\Http\Controllers;

use App\Models\blokRuangan;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Presence;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AbsensiMahasiswa extends Controller
{
    public function index()
    {
        $mergedData = User::leftJoin('presences', function ($join) {
            $join->on('users.id', '=', 'presences.user_id')
                ->whereDate('presences.presence_date', now()->toDateString());
        })
        ->where('users.role_id', 3)
        ->select('users.id', 'users.nim', 'users.name', 'users.status')
        ->selectRaw('GROUP_CONCAT(presences.presence_masuk) as presence_masuk')
        ->selectRaw('GROUP_CONCAT(presences.presence_keluar) as presence_keluar')
        ->orderByRaw("FIELD(users.status, 'diluar', 'telat', 'didalam')")
        ->groupBy('users.id', 'users.nim', 'users.name', 'users.status')
        ->paginate(10);

        $blokRuangan = blokRuangan::all();

        $title = "Riwayat Absensi Mahasiswa";

        return view('admin.absensi-mahasiswa', compact('mergedData', 'title', 'blokRuangan'));
    }

    public function getNomorRuangan(Request $request)
    {
        $blokRuanganId = $request->input('blok_ruangan');

        // Mencari nomor kamar dari user dengan blok_ruangan_id yang sama dan role_id = 3
        $nomorRuangan = User::where('blok_ruangan_id', $blokRuanganId)
            ->where('role_id', 3)
            ->whereNotNull('no_kamar')
            ->groupBy('no_kamar')
            ->orderBy('no_kamar', 'asc')
            ->pluck('no_kamar');

        return response()->json($nomorRuangan);
    }

    public function getDataAbsen(Request $request){
        $blokRuanganId = $request->input('blok_ruangan');
        $nomorRuangan = $request->input('nomor_ruangan');

        $mergedData = User::leftJoin('presences', function ($join) {
            $join->on('users.id', '=', 'presences.user_id')
                ->whereDate('presences.presence_date', now()->toDateString());
        })
        ->where('users.role_id', 3)
        ->select('users.id', 'users.nim', 'users.name', 'users.status')
        ->selectRaw('GROUP_CONCAT(presences.presence_masuk) as presence_masuk')
        ->selectRaw('GROUP_CONCAT(presences.presence_keluar) as presence_keluar')
        ->orderByRaw("FIELD(users.status, 'diluar', 'telat', 'didalam')");
    
        // Filter berdasarkan blokRuanganId jika ada
        if ($blokRuanganId) {
            $mergedData->where('users.blok_ruangan_id', $blokRuanganId);
        }
        
        // Filter berdasarkan nomorRuangan jika ada
        if ($nomorRuangan) {
            $mergedData->where('users.no_kamar', $nomorRuangan);
        }
        
        $mergedData = $mergedData->groupBy('users.id', 'users.nim', 'users.name', 'users.status')
            ->paginate(10);
        
        return response()->json($mergedData);

    }
}
