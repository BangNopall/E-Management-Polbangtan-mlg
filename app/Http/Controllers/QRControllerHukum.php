<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\JenisPelanggaran;
use Illuminate\Routing\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\Pelanggaran;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRControllerHukum extends Controller
{
    public function qrhukum()
    {
        $user = Auth::user();
        $status = Auth::user()->status;

        $validateQR = [
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'scanner' => 'pelanggaran'
        ];

        $title = "QR Hukum";

        $json = json_encode($validateQR);
        $QrCode = QrCode::size(400)->eye('circle')->generate($json);

        $status = Pelanggaran::where('user_id', $user->id)
            ->where('statusPelanggaran', 'scanned')
            ->select('id', 'statusPelanggaran')
            ->first();

        // jika ada semua data user di tabel yang null, redirect ke halaman profil
        if ($user->blok_ruangan_id == null && $user->prodi_id == null && $user->kelas_id == null && $user->no_kamar == null) {
            return redirect('/dashboard/profil')->with('error', 'Silahkan Lengkapi Data Profil Anda Terlebih Dahulu');
        }

        return view('qrhukum', compact('user', 'QrCode', 'title', 'status'));
    }

    public function scanCamPelatih()
    {
        $title = 'Kamera Scan Pelanggaran';
        $user = Auth::user();
        return view('admin.kamera-pelatih', compact('title', 'user'));
    }

    public function scanCamPelatihStore(Request $request)
    {
        if ($request->scanner == 'absensi') {
            return redirect(route('admin.scanCamPelatih'))->with('error', 'Anda Tidak Dapat Melakukan Presensi Pada Scanner Pelanggaran');
        } else {
            $oldRequest = $request;
            // Validasi data berdasarkan Hari Dan jam 
            $currentTimeParsed = $request->time;
            $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);
    
            $timeNow = Carbon::now();
            $timeDifference = $timeNow->diffInSeconds($parsedTime);
            $maxDifference = 30;
            if ($timeDifference <= $maxDifference) {
                $request->validate([
                    'user_id' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                ]);
                $dataPelanggaranBaru = new Pelanggaran;
                $dataPelanggaranBaru->user_id = $request->user_id;
                $dataPelanggaranBaru->date = $request->date;
                $dataPelanggaranBaru->time = $request->time;
                $dataPelanggaranBaru->statusPelanggaran = 'scanned';
                $dataPelanggaranBaru->save();
    
                return redirect()->route('admin.scanCamPelatih')->with('success', 'data berhasil di tambahkan');
            } else {
                return redirect()->route('admin.scanCamPelatih')->with('error', 'QR Code Expired');
            }
        }
    }
}
