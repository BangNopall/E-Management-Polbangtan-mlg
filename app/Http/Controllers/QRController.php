<?php

namespace App\Http\Controllers;

use App\Models\JadwalKegiatanAsrama;
use App\Models\PresensiApel;
use App\Models\PresensiUpacara;
use Carbon\Carbon;
use App\Models\Presence;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PresensiSenam;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRController extends Controller
{
    // new clean code kodeqr()
    public function kodeqr()
    {
        $user = Auth::user();
        $status = Auth::user()->status;
        if ($status == 'diluar' || $status == 'izin') {
            $editStatus = 'didalam';
        } elseif ($status == 'didalam') {
            $editStatus = 'diluar';
        } else {
            $editStatus = 'telat';
        }

        $validateQR = [
            'user_id' => $user->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'status' => $editStatus,
            'scanner' => 'absensi'
        ];

        $title = "Kode QR";

        $jsonRaw = json_encode($validateQR);
        $encryptedPayload = Crypt::encryptString($jsonRaw);
        
        $payloadWrapper = [
            'payload' => $encryptedPayload,
            // Fallback fields mostly left empty for legacy scanners
            'user_id' => null,
            'date' => null,
            'time' => null,
            'status' => null,
            'scanner' => null
        ];

        $json = json_encode($payloadWrapper);
        $QrCode = QrCode::size(400)->eye('circle')->generate($json);

        $getJadwalKegiatan = JadwalKegiatanAsrama::where('tanggal_kegiatan', Carbon::now()->format('Y-m-d'))
            ->where('blok_id', $user->blok_ruangan_id)
            ->select('id', 'jenis_kegiatan', 'mulai_acara', 'selesai_acara')
            ->get();
        foreach ($getJadwalKegiatan as $jadwal) {
            $presensi = null;
            if ($jadwal->jenis_kegiatan == 'Apel') {
                $presensi = PresensiApel::where('user_id', $user->id)
                    ->where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->select('status_kehadiran')
                    ->first();
            } elseif ($jadwal->jenis_kegiatan == 'Senam') {
                $presensi = PresensiSenam::where('user_id', $user->id)
                    ->where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->select('status_kehadiran')
                    ->first();
            } elseif ($jadwal->jenis_kegiatan == 'Upacara') {
                $presensi = PresensiUpacara::where('user_id', $user->id)
                    ->where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->select('status_kehadiran')
                    ->first();
            }

            // Tambahkan status presensi ke jadwal
            $jadwal->status_kehadiran = $presensi ? $presensi->status_kehadiran : null;
        }

        // jika data di tabel user null maka redirect ke halaman profil
        if ($user->blok_ruangan_id == null && $user->prodi_id == null && $user->kelas_id == null && $user->no_kamar == null) {
            return redirect('/dashboard/profil')->with('error', 'Silahkan Lengkapi Data Profil Anda Terlebih Dahulu');
        }
        $time = Carbon::now()->format('H:i:s');
        return view('kodeqr', compact('user', 'QrCode', 'title', 'getJadwalKegiatan', 'time'));
    }

    // optimalisasi admin.kamera()
    public function presense(Request $request)
    {
        if ($request->payload) {
            try {
                $decryptedJson = Crypt::decryptString($request->payload);
                $payloadData = json_decode($decryptedJson, true);
                if (is_array($payloadData)) {
                    $request->merge($payloadData);
                } else {
                    return redirect(route('admin.kamera'))->with('error', 'Format QR Code tidak valid.');
                }
            } catch (\Exception $e) {
                return redirect(route('admin.kamera'))->with('error', 'Kode QR tidak valid atau sudah kadaluarsa (Gagal Dekripsi).');
            }
        }

        if ($request->scanner == 'pelanggaran') {
            return redirect(route('admin.kamera'))->with('error', 'Anda Tidak Dapat Melakukan Pelanggaran Pada Scanner Presensi keluar masuk asrama');
        } else {
            $oldRequest = $request;
            $currentTimeParsed = $request->time;
            $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);

            $timeNow = Carbon::now();
            $timeDifference = $timeNow->diffInSeconds($parsedTime);
            $maxDifference = 30;

            if ($timeDifference <= $maxDifference) {
                $request = $oldRequest;
                $attendance = Attendance::where('date', $request['date'])->first();
                $currentTime = $request->time;
                if (!$attendance) {
                    $today = Carbon::now()->format('Y-m-d');
                    $yesterday = Carbon::now()->subDay()->format('Y-m-d');
                    $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

                    if ($request->date == $yesterday) {
                        return redirect(route('admin.kamera'))->with('error', 'Absensi untuk hari kemarin tidak diizinkan');
                    } elseif ($request->date == $tomorrow) {
                        return redirect(route('admin.kamera'))->with('error', 'Absensi untuk hari besok tidak diizinkan');
                    } else {
                        if ($request->date == $today) {
                            $createAttendance = [
                                'title' => 'Absensi Harian',
                                'date' => $today,
                                'start_time' => '06:00:00',
                                'end_time' => '22:00:00',
                            ];
                            Attendance::create($createAttendance);
                            return redirect(route('admin.kamera'))->with('error', 'Terjadi Missing Data, Silahkan Coba kembali');
                        }
                    }
                }
                if ($attendance) {
                    // BYPASS: if ($currentTime <= $attendance->start_time) {
                    //    return redirect(route('admin.kamera'))->with('error', 'Absensi Belum di buka');
                    // }

                    if (false /* BYPASS: $currentTime >= $attendance->end_time */) {
                        $getStatus = $request->status;
                        if ($getStatus == 'didalam') {
                            $cariPresence = Presence::where('user_id', $request->user_id)
                                ->where('attendance_id', $attendance->id)
                                ->where('presence_date', $request->date)
                                ->latest()
                                ->first();
                            if (!$cariPresence) {
                                return redirect(route('admin.kamera'))->with('error', 'Anda Belum Melakukan Absensi hari ini pada jam kerja system');
                            }
                            if ($cariPresence) {
                                $izinAktif = app(\App\Services\Izin\IzinGateResolver::class)->aktifUntuk($request->user_id, Carbon::now());
                                if ($izinAktif) {
                                    app(\App\Services\Izin\PengajuanIzinService::class)->catatScanGerbang($izinAktif, User::find($request->user_id), Carbon::now(), $getStatus);
                                    $presenceData = [
                                        'user_id' => $request->user_id,
                                        'attendance_id' => $attendance->id,
                                        'presence_date' => $request->date,
                                        'is_late' => 0,
                                        'log_status' => 'izin',
                                        'presence_masuk' => $request->time,
                                    ];
                                    Presence::where('id', $cariPresence->id)->update($presenceData);
                                    User::where('id', $request->user_id)->update(['status' => 'izin']);
                                    return redirect(route('admin.kamera'))->with('success', 'Absensi izin berhasil tercatat');
                                }
                                $getStatus = 'telat';
                                $presenceData = [
                                    'user_id' => $request->user_id,
                                    'attendance_id' => $attendance->id,
                                    'presence_date' => $request->date,
                                    'is_late' => 1,
                                    'log_status' => 'telat'
                                ];

                                $userData = [
                                    'status' => $getStatus,
                                ];

                                $cariPresence = Presence::where('user_id', $request->user_id)
                                    ->where('attendance_id', $attendance->id)
                                    ->where('presence_date', $request->date)
                                    ->latest()
                                    ->first();
                                $presenceData['presence_masuk'] = $request->time;

                                Presence::where('id', $cariPresence->id)->update($presenceData);
                                User::where('id', $request->user_id)->update($userData);
                                return redirect(route('admin.kamera'))->with('success', 'Anda telat absensi hari ini');
                            }
                        }
                        if ($getStatus == 'telat') {
                            return redirect(route('admin.kamera'))->with('success', 'Anda sudah Melakukan Absensi Namun telat absensi hari ini');
                        }
                    }

                    if (true /* BYPASS: $currentTime >= $attendance->start_time && $currentTime <= $attendance->end_time */) {
                        $getStatus = $request->status;

                        $izinAktif = app(\App\Services\Izin\IzinGateResolver::class)->aktifUntuk($request->user_id, Carbon::now());
                        $isIzinTerlambat = false;
                        if ($izinAktif) {
                            $izinUpdated = app(\App\Services\Izin\PengajuanIzinService::class)->catatScanGerbang($izinAktif, User::find($request->user_id), Carbon::now(), $getStatus);
                            if ($izinUpdated && $izinUpdated->status === 'terlambat') {
                                $isIzinTerlambat = true;
                            }
                        }

                        $presenceData = [
                            'user_id' => $request->user_id,
                            'attendance_id' => $attendance->id,
                            'presence_date' => $request->date,
                        ];

                        $userData = [
                            'status' => ($izinAktif && $getStatus == 'diluar') ? 'izin' : $getStatus,
                        ];

                        $cariPresence = Presence::where('user_id', $request->user_id)
                            ->where('attendance_id', $attendance->id)
                            ->where('presence_date', $request->date)
                            ->latest()
                            ->first();

                        if ($cariPresence) {
                            $checkOnePresence = Presence::where('user_id', $request->user_id)
                                ->where('attendance_id', $attendance->id)
                                ->where('presence_date', $request->date)
                                ->first();

                            if ($getStatus == 'diluar') {
                                if ($checkOnePresence->presence_masuk != null) {
                                    $presenceData['presence_keluar'] = $request->time;
                                    $presenceData['presence_masuk'] = null;
                                    $presenceData['log_status'] = ($izinAktif ?? false) ? 'izin' : 'diluar';
                                    $is_active = [
                                        'is_active' => 0,
                                    ];
                                    Presence::where('id', $cariPresence->id)->update($is_active);
                                    Presence::create($presenceData);
                                    User::where('id', $request->user_id)->update($userData);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama');
                                }
                                if ($checkOnePresence->presence_masuk == null) {
                                    $presenceData['presence_keluar'] = $request->time;
                                    $presenceData['log_status'] = ($izinAktif ?? false) ? 'izin' : 'diluar';
                                    Presence::where('id', $cariPresence->id)->update($presenceData);
                                    User::where('id', $request->user_id)->update($userData);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama Lagi Hari Ini');
                                }
                            }
                            if ($getStatus == 'didalam') {
                                $presenceData['presence_masuk'] = $request->time;
                                if ($isIzinTerlambat) {
                                    $presenceData['log_status'] = 'telat';
                                    $presenceData['is_late'] = 1;
                                    Presence::where('id', $cariPresence->id)->update($presenceData);
                                    User::where('id', $request->user_id)->update(['status' => 'didalam']);
                                    return redirect(route('admin.kamera'))->with('error', 'Mahasiswa Berhasil Masuk Asrama, namun TERLAMBAT kembali dari izin resmi (Batas: ' . optional($izinAktif->waktu_kembali)->format('d M H:i') . '). Pelanggaran otomatis dicatat.');
                                }
                                $presenceData['log_status'] = 'didalam';
                                Presence::where('id', $cariPresence->id)->update($presenceData);
                                User::where('id', $request->user_id)->update($userData);
                                return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Masuk Asrama ( Terimakasih Sudah Tidak Telat )');
                            }
                            if ($getStatus == 'telat') {
                                $presenceData['presence_masuk'] = $request->time;
                                $presenceData['log_status'] = 'telat';
                                $userDataTelat = [
                                    'status' => 'didalam',
                                ];
                                Presence::where('id', $cariPresence->id)->update($presenceData);
                                User::where('id', $request->user_id)->update($userDataTelat);
                                return redirect(route('admin.kamera'))->with('error', 'Silahkan Coba Kembali ( Status anda kemarin Telat )');
                            }
                        }
                        if (!$cariPresence) {
                            // create data
                            if ($getStatus == 'diluar') {
                                $presenceData['presence_masuk'] = null;
                                $presenceData['presence_keluar'] = $request->time;
                                $presenceData['log_status'] = ($izinAktif ?? false) ? 'izin' : 'diluar';
                                Presence::create($presenceData);
                                User::where('id', $request->user_id)->update($userData);
                                return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama');
                            }


                            if ($getStatus == 'didalam') {
                                $userDataSiswaDiluar = [
                                    'status' => 'didalam',
                                ];

                                $yesterday = Carbon::now()->subDay()->format('Y-m-d');
                                $cariPresensiKemarin = Presence::where('user_id', $request->user_id)
                                    ->where('presence_date', $yesterday)
                                    ->latest()
                                    ->first();

                                if ($cariPresensiKemarin) {

                                    $cariPresensihariIni = Presence::where('user_id', $request->user_id)
                                        ->where('presence_date', Carbon::now()->format('Y-m-d'))
                                        ->latest()
                                        ->first(); 

                                    $cariAttandeHariIni = Attendance::where('date', Carbon::now()->format('Y-m-d'))->first();
                                    $presenceDataDiluarKemarin = [
                                        'user_id' => $request->user_id,
                                        'attendance_id' => $cariAttandeHariIni->id,
                                        'presence_date' => Carbon::now()->format('Y-m-d'),
                                        'presence_masuk' => $request->time,
                                        'log_status' => $isIzinTerlambat ? 'telat' : 'didalam',
                                        'is_late' => $isIzinTerlambat ? 1 : 0,
                                    ];
                                    Presence::create($presenceDataDiluarKemarin);
                                    User::where('id', $request->user_id)->update($userDataSiswaDiluar);
                                    if ($isIzinTerlambat) {
                                        return redirect(route('admin.kamera'))->with('error', 'Mahasiswa Berhasil Masuk Asrama, namun TERLAMBAT kembali dari izin resmi (Batas: ' . optional($izinAktif->waktu_kembali)->format('d M H:i') . '). Pelanggaran otomatis dicatat.');
                                    }
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Masuk Asrama (Kembali dari Luar)');
                                }

                                if (!$cariPresensiKemarin) {
                                    $cariTerakhirAbsen = Presence::where('user_id', $request->user_id)
                                        ->whereIn('log_status', ['diluar', 'izin'])
                                        ->where('is_active', 1)
                                        ->latest()
                                        ->first();

                                    if ($cariTerakhirAbsen) {
                                        $startDatediluar = Carbon::parse($cariTerakhirAbsen->presence_date);
                                        $endDatediluar = Carbon::now();
                                        $numberOfDays = $startDatediluar->diffInDays($endDatediluar);
                                        $lastStatus = $cariTerakhirAbsen->log_status;

                                        $cariTerakhirAbsen->delete();

                                        for ($i = 0; $i <= $numberOfDays; $i++) {
                                            $currentDate = $startDatediluar->copy()->addDays($i)->format('Y-m-d');
                                            
                                            // Get or Create Attendance for this specific past date
                                            $pastAttendance = Attendance::firstOrCreate(
                                                ['date' => $currentDate],
                                                [
                                                    'title' => 'Absensi Harian',
                                                    'start_time' => '06:00:00',
                                                    'end_time' => '22:00:00',
                                                ]
                                            );

                                            $existingPresence = Presence::where('user_id', $request->user_id)
                                                ->where('attendance_id', $pastAttendance->id)
                                                ->where('presence_date', $currentDate)
                                                ->first();

                                            if (!$existingPresence) {
                                                $presenceDataDiluarBeberapaHari = [
                                                    'user_id' => $request->user_id,
                                                    'attendance_id' => $pastAttendance->id,
                                                    'presence_date' => $currentDate,
                                                    'presence_keluar' => $i == 0 ? $request->time : null, // Only first day might have actual time
                                                    'log_status' => $lastStatus,
                                                ];

                                                Presence::create($presenceDataDiluarBeberapaHari);
                                            }
                                        }

                                        return redirect(route('admin.kamera'))->with('error', '( SILAHKAN COBA KEMBALI !!! ) Anda berada diluar asrama selama beberapa hari dan data sudah direkam.');
                                    }
                                }

                                if ($getStatus == 'telat') {
                                    $presenceData['presence_masuk'] = null;
                                    $presenceData['presence_keluar'] = $request->time;
                                    $presenceData['log_status'] = 'diluar';
                                    $userDataTelat = [
                                        'status' => 'diluar',
                                    ];
                                    Presence::create($presenceData);
                                    User::where('id', $request->user_id)->update($userDataTelat);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama');
                                }
                            }

                            if ($getStatus == 'telat') {
                                $presenceData['presence_masuk'] = null;
                                $presenceData['presence_keluar'] = $request->time;
                                $presenceData['log_status'] = 'diluar';

                                $userDataTelat = [
                                    'status' => 'diluar',
                                ];
                                Presence::create($presenceData);
                                User::where('id', $request->user_id)->update($userDataTelat);
                                return redirect(route('admin.kamera'))->with('error', 'Silahkan Coba Kembali ( Status anda kemarin Telat )');
                            }
                        }
                    }
                }

                return redirect(route('admin.kamera'))->with('error', 'Presensi hari ini tidak di temukan dan jam kerja sistem sudah berakhir coba lagi pada pukul 06:00:00');
            }

            return redirect(route('admin.kamera'))->with('error', 'MAX Time Lebih Dari 30 detik');
        }
    }


}
