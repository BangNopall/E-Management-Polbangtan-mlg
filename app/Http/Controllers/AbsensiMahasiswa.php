<?php

namespace App\Http\Controllers;

use App\Models\BlokRuangan;
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
            ->paginate(20);

        $blokRuangan = BlokRuangan::all();

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

    public function getDataAbsen(Request $request)
    {
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
            ->paginate(20);

        return response()->json($mergedData);
    }

    public function dataAbsenKeluarShow()
    {
        $Users = User::where('role_id', 3)->with('kelas', 'blok')
            ->select('id', 'name', 'nim', 'kelas_id', 'blok_ruangan_id')
            ->paginate(20);
        return view('admin.data-absenkeluar', compact('Users'));
    }

    public function dataAbsenKeluarDetail($id)
    {
        $user = User::where('id', $id)->with('kelas', 'blok')->first();
        // dd($user);
        $today = date('Y-m-d');
        $paginationControl = 20;
        $dataPresensi = Presence::where('user_id', $id)
            ->orderBy('presence_date', 'desc')
            ->paginate(20);
        $dataPresensi->each(function ($presensi) {
            $presensi->formatted_date = Carbon::parse($presensi->presence_date)->format('d F Y');
        });

        $dataTotal = [];
        $dataTotal['totalDiluar'] = Presence::where('user_id', $id)
            ->where('log_status', 'diluar')
            ->count();
        $dataTotal['totalDidalam'] = Presence::where('user_id', $id)
            ->where('log_status', 'didalam')
            ->count();
        $dataTotal['totalTelat'] = Presence::where('user_id', $id)
            ->where('log_status', 'telat')
            ->count();
        $dataTotal['totalPresensi'] = Presence::where('user_id', $id)
            ->count();

        return view('admin.detail-absenkeluar', compact('user', 'dataPresensi', 'dataTotal'));
    }

    public function dataAbsenKeluarSearch(Request $request)
    {
        $Users = User::where('role_id', 3)
            ->with('kelas', 'blok')
            ->select('id', 'name', 'nim', 'kelas_id', 'blok_ruangan_id')
            ->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('nim', 'like', '%' . $request->search . '%')
                    ->orWhereHas('kelas', function ($query) use ($request) {
                        $query->where('nama_kelas', 'like', '%' . $request->search . '%');
                    })
                    ->orWhereHas('blok', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
            })
            ->paginate(20);

        // Load the table view and return it as part of the JSON response
        $table = view('admin.partials.data_absen_table', compact('Users'))->render();

        return response()->json(['table' => $table]);
    }
}
