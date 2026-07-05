<?php

namespace App\Http\Controllers;


use App\Models\jadwalKegiatanAsrama;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\PresensiUpacara;
use Carbon\Carbon;
use App\Models\User;
use Nette\Utils\Json;
use App\Models\Presence;

use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use App\Models\JadwalPetugas;
use App\Models\LoginPermission;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;

use App\Http\Controllers\Controller;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends Controller
{
    public function index()
    {
        $user = User::all();

        $title = "Home";

        // Total user
        $totaluser = $user->count();
        $activeuser = LoginPermission::where('is_login', 1)->count();

        $status = Auth::user();

        $userAuth = Auth::user();

        $rekapBulanan = Presence::where('user_id', $userAuth->id)
            ->whereMonth('presence_date', now()->format('m'))
            ->whereYear('presence_date', now()->format('Y'))
            ->where('log_status', 'diluar')
            ->distinct('presence_date');
        $total_days_bulanan_diluar = $rekapBulanan->count();

        $rekapBulananTelat = Presence::where('user_id', $userAuth->id)
            ->whereMonth('presence_date', now()->format('m'))
            ->whereYear('presence_date', now()->format('Y'))
            ->where('log_status', 'telat')
            ->distinct('presence_date');
        $total_days_bulanan_telat = $rekapBulananTelat->count();


        $jadwalPiket = JadwalPetugas::where('date', now()->format('y-m-d'))->first();
        if ($jadwalPiket != null) {
            $petugas1 = User::where('id', $jadwalPiket->petugas1_id)->value('name');
            $petugas2 = User::where('id', $jadwalPiket->petugas2_id)->value('name');
            if ($jadwalPiket->petugas1_id == null) {
                $petugas1 = "Belum ada";
                if ($jadwalPiket->petugas2_id == null) {
                    $petugas2 = "Belum ada";
                }
            }
        } else {
            $petugas1 = "Belum ada";
            $petugas2 = "Belum ada";
        }

        $GETjenis_pelanggaran_id = Pelanggaran::where('user_id', $userAuth->id)
            ->whereIn('statusPelanggaran', ['progressing', 'Done'])
            ->select('jenis_pelanggaran_id')
            ->get();

        if ($GETjenis_pelanggaran_id == null) {
            $total_point = 0;
        } elseif ($GETjenis_pelanggaran_id != null) {
            $total_point = 0;

            foreach ($GETjenis_pelanggaran_id as $jenis_pelanggaran_id) {
                $total_point += JenisPelanggaran::where('id', $jenis_pelanggaran_id->jenis_pelanggaran_id)->value('poin');
            }
        }

        $id = Auth::user()->id;
        $today = Carbon::now()->format('Y-m-d');

        $dataKegiatanUpacara = PresensiUpacara::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                jadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_upacaras.jadwalKegiatanAsrama_id')
            )
            ->get();
        $dataKegiatanApel = PresensiApel::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_apels.jadwalKegiatanAsrama_id')
            )
            ->get();
        $dataKegiatanSenam = PresensiSenam::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_senams.jadwalKegiatanAsrama_id')
            )
            ->get();
        $rekapKegiatan = [
            'UPACARA' => [
                'Hadir' => $dataKegiatanUpacara->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanUpacara->where('status_kehadiran', 'Alpha')->count()
            ],
            'APEL' => [
                'Hadir' => $dataKegiatanApel->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanApel->where('status_kehadiran', 'Alpha')->count()
            ],
            'SENAM' => [
                'Hadir' => $dataKegiatanSenam->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanSenam->where('status_kehadiran', 'Alpha')->count()
            ]
        ];

        return view('index', compact('totaluser', 'activeuser', 'status', 'title', 'petugas1', 'petugas2', 'total_days_bulanan_diluar', 'total_days_bulanan_telat', 'total_point', 'rekapKegiatan'));
    }
    public function riwayat(Request $request)
    {
        $filter = $request->input('filter', 'all');
        $query = Presence::where('user_id', Auth::user()->id);

        if (!$filter) {
            $filter = 'all';
        }

        $query = Presence::where('user_id', Auth::user()->id);

        switch ($filter) {
            case '7-days':
                $query->whereBetween('presence_date', [now()->subDays(7), now()]);
                break;
            case '14-days':
                $query->whereBetween('presence_date', [now()->subDays(14), now()]);
                break;
            case '30-days':
                $query->whereBetween('presence_date', [now()->subDays(30), now()]);
                break;
            case '6-month':
                $query->whereBetween('presence_date', [now()->subMonths(6), now()]);
                break;
            default:
                break;
        }

        $title = "Riwayat Absen";

        $riwayat = $query->orderBy('presence_date', 'desc')
            ->distinct('presence_date')->get();
        return view('riwayat', compact('riwayat', 'filter', 'title'));
    }

    public function getPresenceDate()
    {
        $user = auth()->user();

        $presenceDates = Presence::where('user_id', $user->id)->select('presence_date')->distinct()->get();

        return response()->json($presenceDates);
    }
}
