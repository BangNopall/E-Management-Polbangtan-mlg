<?php

namespace App\Http\Controllers;

use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\jadwalKegiatanAsrama;
use App\Models\PresensiUpacara;
use Illuminate\Support\Facades\Auth;

class QRControllerKegiatan extends Controller
{
    public function kameraKegiatanUpacaraShow()
    {
        $user = Auth::user();

        return view('admin.kamera-upacara', compact('user'));
    }

    public function kameraKegiatanApelShow()
    {
        $user = Auth::user();

        return view('admin.kamera-apel', compact('user'));
    }

    public function kameraKegiatanSenamShow()
    {
        $user = Auth::user();

        return view('admin.kamera-senam', compact('user'));
    }

    public function kameraKegiatanUpacaraApi(Request $request)
    {
        if ($request->scanner == 'absensi') {
            try {
                $request->validate([
                    'user_id' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                    'scanner' => 'required'
                ]);

                // validasi jam
                $currentTimeParsed = $request->time;
                $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);

                $timeNow = Carbon::now();
                $timeDifference = $timeNow->diffInSeconds($parsedTime);
                $maxDifference = 30;
                if ($timeDifference <= $maxDifference) {
                    list($getUser, $jenis_kegiatan) = $this->validateQR($request, 'Upacara');
                    return redirect(route('admin.kameraKegiatanUpacaraShow'))->with('success', 'Presensi ' . $jenis_kegiatan . ' Untuk Mahasiswa Atas Nama ' . $getUser->name . ' Berhasil Dilakukan.');
                } elseif ($timeDifference > $maxDifference) {
                    return redirect(route('admin.kameraKegiatanUpacaraShow'))->with('error', 'QR Code Expired');
                }
            } catch (\Exception $e) {
                return redirect(route('admin.kameraKegiatanUpacaraShow'))->with('error', $e->getMessage());
            }
        } else {
            return redirect(route('admin.kameraKegiatanApelShow'))->with('error', 'Anda tidak dapat melakukan scan kode QR Pelanggaran pada scanner kegiatan.');
        }
    }

    public function kameraKegiatanApelApi(Request $request)
    {
        if ($request->scanner == 'absensi') {
            try {
                $request->validate([
                    'user_id' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                    'scanner' => 'required'
                ]);

                // validasi jam
                $currentTimeParsed = $request->time;
                $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);

                $timeNow = Carbon::now();
                $timeDifference = $timeNow->diffInSeconds($parsedTime);
                $maxDifference = 30;
                if ($timeDifference <= $maxDifference) {
                    list($getUser, $jenis_kegiatan) = $this->validateQR($request, 'Apel');
                    return redirect(route('admin.kameraKegiatanApelShow'))->with('success', 'Presensi ' . $jenis_kegiatan . ' Untuk Mahasiswa Atas Nama ' . $getUser->name . ' Berhasil Dilakukan.');
                } elseif ($timeDifference > $maxDifference) {
                    return redirect(route('admin.kameraKegiatanApelShow'))->with('error', 'QR Code Expired');
                }
            } catch (\Exception $e) {
                return redirect(route('admin.kameraKegiatanApelShow'))->with('error', $e->getMessage());
            }
        } else {
            return redirect(route('admin.kameraKegiatanApelShow'))->with('error', 'Anda tidak dapat melakukan scan kode QR Pelanggaran pada scanner kegiatan.');
        }
    }

    public function kameraKegiatanSenamApi(Request $request)
    {
        if ($request->scanner == 'absensi') {
            try {
                $request->validate([
                    'user_id' => 'required',
                    'date' => 'required',
                    'time' => 'required',
                    'scanner' => 'required'
                ]);

                // validasi jam
                $currentTimeParsed = $request->time;
                $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);

                $timeNow = Carbon::now();
                $timeDifference = $timeNow->diffInSeconds($parsedTime);
                $maxDifference = 30;
                if ($timeDifference <= $maxDifference) {
                    list($getUser, $jenis_kegiatan) = $this->validateQR($request, 'Senam');
                    return redirect(route('admin.kameraKegiatanSenamShow'))->with('success', 'Presensi ' . $jenis_kegiatan . ' Untuk Mahasiswa Atas Nama ' . $getUser->name . ' Berhasil Dilakukan.');
                } elseif ($timeDifference > $maxDifference) {
                    return redirect(route('admin.kameraKegiatanSenamShow'))->with('error', 'QR Code Expired');
                }
            } catch (\Exception $e) {
                return redirect(route('admin.kameraKegiatanSenamShow'))->with('error', $e->getMessage());
            }
        } else {
            return redirect(route('admin.kameraKegiatanSenamShow'))->with('error', 'Anda tidak dapat melakukan scan kode QR Pelanggaran pada scanner kegiatan.');
        }
    }

    private function validateQR($request, $jenis_kegiatan)
    {
        $getUser = User::with('blok')->where('id', $request->user_id)->select('id', 'blok_ruangan_id', 'name')->first();
        if ($getUser === null) {
            throw new \Exception('Mahasiswa tidak ditemukan');
        }

        $getJadwal = jadwalKegiatanAsrama::where('blok_id', $getUser->blok_ruangan_id)
            ->where('tanggal_kegiatan', $request->date)
            ->where('jenis_kegiatan', $jenis_kegiatan)
            ->get();
        if ($getJadwal === null) {
            $formatted_date = Carbon::parse($request->date)->format('d F Y');
            throw new \Exception(
                'Tidak Ada Kegiatan ' . $jenis_kegiatan . ' Pada Tanggal ' . $formatted_date . ' Untuk Blok ' . $getUser->blok->name
            );
        }
        // dd($getJadwal);
        if ($getJadwal->count() == 1) {
            $getJadwal = $getJadwal->first();
        } else {
            $getJadwal = $getJadwal->where('mulai_acara', '<=', $request->time)
                ->where('selesai_acara', '>=', $request->time)
                ->first();
        }
        // dd($getJadwal);

        // validasi mulai_acara dan selesai_acara
        $mulai_acara = $getJadwal->mulai_acara;
        $selesai_acara = $getJadwal->selesai_acara;

        if ($request->time >= $mulai_acara && $request->time <= $selesai_acara) {
            $this->pushPresense($getJadwal, $getUser, $jenis_kegiatan, $request);
            return [$getUser, $jenis_kegiatan];
        } elseif ($request->time < $mulai_acara) {
            throw new \Exception('Kegiatan ' . $jenis_kegiatan . ' Belum Dimulai');
        } elseif ($request->time > $selesai_acara) {
            throw new \Exception('Kegiatan ' . $jenis_kegiatan . ' Telah Selesai');
        }
    }

    private function pushPresense($getJadwal, $getUser, $jenis_kegiatan, $request)
    {
        if ($jenis_kegiatan == 'Upacara') {
            $getPresensi = PresensiUpacara::where('jadwalKegiatanAsrama_id', $getJadwal->id)
                ->where('user_id', $getUser->id)
                ->first();
            if ($getPresensi->status_kehadiran == 'Alpha') {
                $getPresensi->jam_kehadiran = $request->time;
                $getPresensi->status_kehadiran = 'Hadir';
                $getPresensi->save();
            } elseif ($getPresensi->status_kehadiran == 'Hadir') {
                throw new \Exception('Anda Sudah Melakukan Presensi');
            } else {
                throw new \Exception('presensi tidak ditemukan');
            }
        } elseif ($jenis_kegiatan == 'Apel') {
            $getPresensi = PresensiApel::where('jadwalKegiatanAsrama_id', $getJadwal->id)
                ->where('user_id', $getUser->id)
                ->first();
            if ($getPresensi->status_kehadiran == 'Alpha') {
                $getPresensi->jam_kehadiran = $request->time;
                $getPresensi->status_kehadiran = 'Hadir';
                $getPresensi->save();
            } elseif ($getPresensi->status_kehadiran == 'Hadir') {
                throw new \Exception('Anda Sudah Melakukan Presensi');
            } else {
                throw new \Exception('presensi tidak ditemukan');
            }
        } elseif ($jenis_kegiatan == 'Senam') {
            $getPresensi = PresensiSenam::where('jadwalKegiatanAsrama_id', $getJadwal->id)
                ->where('user_id', $getUser->id)
                ->first();
            if ($getPresensi->status_kehadiran == 'Alpha') {
                $getPresensi->jam_kehadiran = $request->time;
                $getPresensi->status_kehadiran = 'Hadir';
                $getPresensi->save();
            } elseif ($getPresensi->status_kehadiran == 'Hadir') {
                throw new \Exception('Anda Sudah Melakukan Presensi');
            } else {
                throw new \Exception('presensi tidak ditemukan');
            }
        } else {
            throw new \Exception('Jenis Kegiatan Tidak Ditemukan');
        }
    }
}
