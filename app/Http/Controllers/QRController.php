<?php

namespace App\Http\Controllers;

use App\Models\jadwalKegiatanAsrama;
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
        if ($status == 'diluar') {
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

        $json = json_encode($validateQR);
        $QrCode = QrCode::size(400)->eye('circle')->generate($json);

        $getJadwalKegiatan = jadwalKegiatanAsrama::where('tanggal_kegiatan', Carbon::now()->format('Y-m-d'))
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
        // dd($request->all());
        if ($request->scanner == 'pelanggaran') {
            return redirect(route('admin.kamera'))->with('error', 'Anda Tidak Dapat Melakukan Pelanggaran Pada Scanner Presensi keluar masuk asrama');
        } else {
            $oldRequest = $request;
            // Validasi data berdasarkan Hari Dan jam 
            $currentTimeParsed = $request->time;
            $parsedTime = Carbon::createFromFormat('H:i:s', $currentTimeParsed);

            $timeNow = Carbon::now();
            $timeDifference = $timeNow->diffInSeconds($parsedTime);
            $maxDifference = 30;

            // dd($timeDifference, $maxDifference);

            if ($timeDifference <= $maxDifference) {
                // dd($timeDifference, $maxDifference);
                $request = $oldRequest;
                $attendance = Attendance::where('date', $request['date'])->first();
                $currentTime = $request->time;
                // dd($request);
                // Validasi Hari 
                if (!$attendance) {
                    $today = Carbon::now()->format('Y-m-d');
                    $yesterday = Carbon::now()->subDay()->format('Y-m-d');
                    $tomorrow = Carbon::now()->addDay()->format('Y-m-d');

                    if ($request->date == $yesterday) {
                        // Jika user mencoba absen untuk hari kemarin
                        // Handle kasus absensi untuk hari kemarin di sini
                        // Misalnya, berikan pesan kesalahan khusus
                        return redirect(route('admin.kamera'))->with('error', 'Absensi untuk hari kemarin tidak diizinkan');
                    } elseif ($request->date == $tomorrow) {
                        // Jika user mencoba absen untuk hari besok
                        // Handle kasus absensi untuk hari besok di sini
                        // Misalnya, berikan pesan kesalahan khusus
                        return redirect(route('admin.kamera'))->with('error', 'Absensi untuk hari besok tidak diizinkan');
                    } else {
                        // Jika tidak ada data absensi untuk hari ini, kemarin, atau besok
                        // Handle kasus absensi untuk hari ini di sini
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
                    if ($currentTime <= $attendance->start_time) {
                        return redirect(route('admin.kamera'))->with('error', 'Absensi Belum di buka');
                    }

                    if ($currentTime >= $attendance->end_time) {
                        // $getStatus = 'telat';
                        $getStatus = $request->status;
                        if ($getStatus == 'didalam') {
                            $cariPresence = Presence::where('user_id', $request->user_id)
                                ->where('attendance_id', $attendance->id)
                                ->where('presence_date', $request->date)
                                ->latest() // Mengurutkan berdasarkan kolom 'created_at' secara descending
                                ->first(); // Mengambil data terbaru (yang pertama dari hasil urutan)
                            if (!$cariPresence) {
                                return redirect(route('admin.kamera'))->with('error', 'Anda Belum Melakukan Absensi hari ini pada jam kerja system');
                            }
                            if ($cariPresence) {
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
                                    ->latest() // Mengurutkan berdasarkan kolom 'created_at' secara descending
                                    ->first(); // Mengambil data terbaru (yang pertama dari hasil urutan)
                                $presenceData['presence_masuk'] = $request->time;

                                Presence::where('id', $cariPresence->id)->update($presenceData);
                                User::where('id', $request->user_id)->update($userData);
                                return redirect(route('admin.kamera'))->with('error', 'Anda telat absensi hari ini');
                            }
                        }
                        if ($getStatus == 'telat') {
                            return redirect(route('admin.kamera'))->with('error', 'Anda sudah Melakukan Absensi Namun telat absensi hari ini');
                        }


                        // return redirect(route('admin.kamera'))->with('error', 'telat absen');
                    }

                    if ($currentTime >= $attendance->start_time && $currentTime <= $attendance->end_time) {
                        $getStatus = $request->status;

                        $presenceData = [
                            'user_id' => $request->user_id,
                            'attendance_id' => $attendance->id,
                            'presence_date' => $request->date,
                        ];

                        $userData = [
                            'status' => $getStatus,
                        ];

                        $cariPresence = Presence::where('user_id', $request->user_id)
                            ->where('attendance_id', $attendance->id)
                            ->where('presence_date', $request->date)
                            ->latest() // Mengurutkan berdasarkan kolom 'created_at' secara descending
                            ->first(); // Mengambil data terbaru (yang pertama dari hasil urutan)
                        // dd($cariPresence);



                        if ($cariPresence) {
                            // check 1 / lebih
                            $checkOnePresence = Presence::where('user_id', $request->user_id)
                                ->where('attendance_id', $attendance->id)
                                ->where('presence_date', $request->date)
                                ->first();
                            // dd($checkOnePresence->presence_keluar);

                            // update data
                            if ($getStatus == 'diluar') {
                                if ($checkOnePresence->presence_masuk != null) {
                                    // dd('update data baru dan hapus data lama');
                                    $presenceData['presence_keluar'] = $request->time;
                                    $presenceData['presence_masuk'] = null;
                                    $presenceData['log_status'] = 'diluar';
                                    $is_active = [
                                        'is_active' => 0,
                                    ];
                                    // dd($presenceData, $userData, $is_active);
                                    Presence::where('id', $cariPresence->id)->update($is_active);
                                    Presence::create($presenceData);
                                    User::where('id', $request->user_id)->update($userData);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama');
                                }
                                if ($checkOnePresence->presence_masuk == null) {
                                    // dd('update data baru');
                                    $presenceData['presence_keluar'] = $request->time;
                                    $presenceData['log_status'] = 'diluar';
                                    Presence::where('id', $cariPresence->id)->update($presenceData);
                                    User::where('id', $request->user_id)->update($userData);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama Lagi Hari Ini');
                                }
                            }
                            if ($getStatus == 'didalam') {
                                $presenceData['presence_masuk'] = $request->time;
                                $presenceData['log_status'] = 'didalam';
                                // dd($presenceData, $userData);
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
                                // dd($presenceData, $userData, 'create data didalam');
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
                                $presenceData['log_status'] = 'diluar';
                                // dd($presenceData, $userData, 'create data keluar');
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
                                    ->latest() // Mengurutkan berdasarkan kolom 'created_at' secara descending
                                    ->first(); // Mengambil data terbaru (yang pertama dari hasil urutan)

                                if ($cariPresensiKemarin) {

                                    $cariPresensihariIni = Presence::where('user_id', $request->user_id)
                                        ->where('presence_date', Carbon::now()->format('Y-m-d'))
                                        ->latest() // Mengurutkan berdasarkan kolom 'created_at' secara descending
                                        ->first(); // Mengambil data terbaru (yang pertama dari hasil urutan)
                                    // Lakukan Sesuatu Untuk DIA agar terdata tidak masuk asrama kemarin

                                    $cariAttandeHariIni = Attendance::where('date', Carbon::now()->format('Y-m-d'))->first();
                                    $presenceDataDiluarKemarin = [
                                        'user_id' => $request->user_id,
                                        'attendance_id' => $cariAttandeHariIni->id,
                                        'presence_date' => Carbon::now()->format('Y-m-d'),
                                        'presence_keluar' => $request->time,
                                        'presence_masuk' => $request->time,
                                        'log_status' => 'didalam',
                                    ];
                                    Presence::create($presenceDataDiluarKemarin);
                                    User::where('id', $request->user_id)->update($userDataSiswaDiluar);
                                    return redirect(route('admin.kamera'))->with('error', 'anda berada diluar asrama kemarin , lakukan presensi kembali untuk masuk asrama ');
                                }

                                // jika tidak kembali selama beberapa hari 
                                if (!$cariPresensiKemarin) {
                                    // Cek apakah ada presensi 'diluar' sebelumnya
                                    $cariTerakhirAbsen = Presence::where('user_id', $request->user_id)
                                        ->where('log_status', 'diluar')
                                        ->where('is_active', 1)
                                        ->latest()
                                        ->first();

                                    if ($cariTerakhirAbsen) {
                                        // Menghitung berapa hari pengguna berada diluar asrama
                                        $startDatediluar = Carbon::parse($cariTerakhirAbsen->presence_date);
                                        $endDatediluar = Carbon::now();
                                        $numberOfDays = $startDatediluar->diffInDays($endDatediluar);

                                        // Menghapus presensi 'diluar' terakhir
                                        $cariTerakhirAbsen->delete();

                                        // Loop melalui setiap hari dan buat presensi
                                        for ($i = 0; $i <= $numberOfDays; $i++) {
                                            $currentDate = $startDatediluar->copy()->addDays($i)->format('Y-m-d');

                                            // Cek apakah presensi untuk tanggal ini sudah ada
                                            $existingPresence = Presence::where('user_id', $request->user_id)
                                                ->where('attendance_id', $attendance->id)
                                                ->where('presence_date', $currentDate)
                                                ->first();

                                            // Jika belum ada, buat presensi baru
                                            if (!$existingPresence) {
                                                $presenceDataDiluarBeberapaHari = [
                                                    'user_id' => $request->user_id,
                                                    'attendance_id' => $attendance->id,
                                                    'presence_date' => $currentDate,
                                                    'presence_keluar' => $request->time,
                                                    'log_status' => 'diluar',
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
                                    // dd($presenceData, $userData, 'create data keluar');
                                    Presence::create($presenceData);
                                    User::where('id', $request->user_id)->update($userDataTelat);
                                    return redirect(route('admin.kamera'))->with('success', 'Anda Berhasil Melakukan Presensi Keluar Asrama');
                                    // return redirect(route('admin.kamera'))->with('error', 'Anda belum Melakukan Presensi Sesuai Jadwal Pada Hari ini');
                                }
                            }

                            if ($getStatus == 'telat') {
                                $presenceData['presence_masuk'] = null;
                                $presenceData['presence_keluar'] = $request->time;
                                $presenceData['log_status'] = 'diluar';

                                $userDataTelat = [
                                    'status' => 'diluar',
                                ];
                                // dd($presenceData, $userData, 'create data didalam');
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
