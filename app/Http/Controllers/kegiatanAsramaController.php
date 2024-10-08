<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\blokRuangan;
use Illuminate\Support\Arr;
use App\Models\PresensiApel;
use Illuminate\Http\Request;
use App\Models\PresensiSenam;
use App\Models\PresensiUpacara;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\jadwalKegiatanAsrama;

class kegiatanAsramaController extends Controller
{

    public function jadwalKegiatanShow()
    {
        $title = 'Jadwal Kegiatan Asrama';
        $daftarJadwalKegiatanAsrama = JadwalKegiatanAsrama::orderByDesc('tanggal_kegiatan')
            ->orderBy('blok_id')
            ->paginate(10);
        $daftarJadwalKegiatanAsrama->each(function ($kegiatan) {
            $kegiatan->formatted_date = Carbon::parse($kegiatan->tanggal_kegiatan)->format('d F Y');
        });
        $blokRuangan = blokRuangan::all();
        $kegiatanAsrama = ['Apel', 'Upacara', 'Senam'];
        $statusKehadiran = ['Hadir', 'Izin', 'Alpha'];

        // dd($daftarJadwalKegiatanAsrama);
        return view('admin.jadwal-kegiatan', compact('title', 'daftarJadwalKegiatanAsrama', 'blokRuangan', 'kegiatanAsrama', 'statusKehadiran'));
    }

    public function jadwalKegiatanFilter(Request $request)
    {
        // dd($request->all());
        $data = $request->all();

        // Mulai query
        $query = JadwalKegiatanAsrama::query();

        // Tambahkan kondisi filter jika parameter ada dalam request
        if (isset($data['blok'])) {
            $query->whereIn('blok_id', $data['blok']);
        }
        if (isset($data['kegiatan'])) {
            $query->whereIn('jenis_kegiatan', $data['kegiatan']);
        }
        if (isset($data['tanggal_kegiatan'])) {
            $query->whereIn('tanggal_kegiatan', Arr::wrap($data['tanggal_kegiatan']));
        }

        // Eksekusi query
        $daftarJadwalKegiatanAsrama = $query->orderBy('blok_id')->paginate(10);
        /** @var \Illuminate\Support\Collection $daftarJadwalKegiatanAsrama */
        $daftarJadwalKegiatanAsrama->each(function ($kegiatan) {
            $kegiatan->formatted_date = Carbon::parse($kegiatan->tanggal_kegiatan)->format('d F Y');
        });
        return view('admin.partials.jadwal_kegiatan_table', compact('daftarJadwalKegiatanAsrama'));
    }

    public function editJadwalKegiatanAsrama(Request $request, $id)
    {
        try {
            $request->validate([
                'mulai_acara' => 'required',
                'selesai_acara' => 'required',
            ]);
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
        }

        try {
            $dataJadwalKegiatan = JadwalKegiatanAsrama::where('id', $id)->first();
            $dataJadwalKegiatan->update([
                'mulai_acara' => $request->mulai_acara,
                'selesai_acara' => $request->selesai_acara,
            ]);
            $dataJadwalKegiatan->save();

            return redirect()->route('admin.jadwalKegiatanShow')->with('success', 'Berhasil mengubah jadwal kegiatan asrama');
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
        }
    }

    public function deleteJadwalKegiatanAsrama(Request $request, $id)
    {
        try {
            $dataJadwalKegiatan = JadwalKegiatanAsrama::where('id', $id)->first();
            $dataJadwalKegiatan->delete();

            return redirect()->route('admin.jadwalKegiatanShow')->with('success', 'Berhasil menghapus jadwal kegiatan asrama');
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
        }
    }

    public function createJadwalKegiatanStore(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'blok' => 'required',
                'kegiatan' => 'required',
                'tanggal_kegiatan' => 'required',
            ]);

            // dd($request->all());
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
        }

        try {
            foreach ($request->blok as $blok) {
                foreach ($request->kegiatan as $kegiatan) {
                    $searchJadwal = JadwalKegiatanAsrama::where('blok_id', $blok)
                        ->where('jenis_kegiatan', $kegiatan)
                        ->where('tanggal_kegiatan', $request->tanggal_kegiatan)
                        ->first();

                    $searchUser = User::where('blok_ruangan_id', $blok)->get();

                    // skip jadwal yang sudah ada
                    // if ($searchJadwal) {
                    //     continue;
                    // }

                    $jadwal = JadwalKegiatanAsrama::create([
                        'blok_id' => $blok,
                        'jenis_kegiatan' => $kegiatan,
                        'tanggal_kegiatan' => $request->tanggal_kegiatan,
                        'mulai_acara' => $request['mulai_acara_' . $kegiatan],
                        'selesai_acara' => $request['selesai_acara_' . $kegiatan],
                    ]);

                    foreach ($searchUser as $user) {
                        // Create a new attendance record in the appropriate table based on the activity type
                        switch ($kegiatan) {
                            case 'Apel':
                                PresensiApel::create([
                                    'jadwalKegiatanAsrama_id' => $jadwal->id,
                                    'user_id' => $user->id,
                                    'status_kehadiran' => 'Alpha',
                                ]);
                                break;
                            case 'Upacara':
                                PresensiUpacara::create([
                                    'jadwalKegiatanAsrama_id' => $jadwal->id,
                                    'user_id' => $user->id,
                                    'status_kehadiran' => 'Alpha',
                                ]);
                                break;
                            case 'Senam':
                                PresensiSenam::create([
                                    'jadwalKegiatanAsrama_id' => $jadwal->id,
                                    'user_id' => $user->id,
                                    'status_kehadiran' => 'Alpha',
                                ]);
                                break;
                        }
                    }
                }
            }
            return redirect()->route('admin.jadwalKegiatanShow')->with('success', 'Berhasil menambahkan jadwal kegiatan asrama');
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
        }
    }
    public function riwayatAktivitasShow()
    {
        $title = 'Riwayat Aktivitas';
        $paginate = 10;
        $dataKegiatanUpacara = PresensiUpacara::with('jadwalKegiatanAsrama')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_upacaras.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginate);
        $dataKegiatanApel = PresensiApel::with('jadwalKegiatanAsrama')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_apels.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginate);
        $dataKegiatanSenam = PresensiSenam::with('jadwalKegiatanAsrama')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_senams.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginate);
        $today = now()->toDateString();
        $now = now()->toTimeString();

        $totalDataKegiatanUpacara = PresensiUpacara::where('user_id', auth()->user()->id)->count();
        $totalDataKegiatanApel = PresensiApel::where('user_id', auth()->user()->id)->count();
        $totalDataKegiatanSenam = PresensiSenam::where('user_id', auth()->user()->id)->count();

        $allDataKegiatan = [
            'dataKegiatanUpacara' => $dataKegiatanUpacara,
            'dataKegiatanApel' => $dataKegiatanApel,
            'dataKegiatanSenam' => $dataKegiatanSenam,
        ];

        foreach ($allDataKegiatan as $jenisKegiatan => $dataKegiatan) {
            /** @var \Illuminate\Support\Collection $dataKegiatan */
            $dataKegiatan->each(function ($kegiatan) use ($today, $now) {
                if ($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan > $today) {
                    $kegiatan->status_acara = 'Upcoming Event';
                }
                if ($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan == $today) {
                    if ($kegiatan->jadwalKegiatanAsrama->mulai_acara <= $now && $kegiatan->jadwalKegiatanAsrama->selesai_acara >= $now) {
                        $kegiatan->status_acara = 'Sedang Berlangsung';
                    } else {
                        $kegiatan->status_acara = 'Acara Selesai';
                    }
                }
                if ($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan < $today) {
                    $kegiatan->status_acara = 'Acara Selesai';
                }

                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });
        }

        $totalDataKegiatan = [
            'totalDataKegiatanUpacara' => $totalDataKegiatanUpacara,
            'totalDataKegiatanApel' => $totalDataKegiatanApel,
            'totalDataKegiatanSenam' => $totalDataKegiatanSenam,
        ];

        arsort($totalDataKegiatan);
        $keyPagination = key($totalDataKegiatan);  // pagination key

        if ($keyPagination === 'totalDataKegiatanUpacara') {
            $keyPagination = 'dataKegiatanUpacara';
        } elseif ($keyPagination === 'totalDataKegiatanApel') {
            $keyPagination = 'dataKegiatanApel';
        } elseif ($keyPagination === 'totalDataKegiatanSenam') {
            $keyPagination = 'dataKegiatanSenam';
        }

        // dd($keyPagination, $totalDataKegiatan, $allDataKegiatan);
        return view('riwayat-kegiatan', compact('title', 'keyPagination', 'allDataKegiatan'));
    }

    public function dataKegiatanWajibShow()
    {
        $Users = User::where('role_id', 3)->with('kelas', 'blok')
            ->select('id', 'name', 'nim', 'kelas_id', 'blok_ruangan_id')
            ->paginate(20);
        // dd($user);
        return view('admin.data-absenkegiatan', compact('Users'));
    }

    public function dataKegiatanWajibSearch(Request $request)
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
        $table = view('admin.partials.data_kegiatan_table', compact('Users'))->render();

        return response()->json(['table' => $table]);
    }

    public function dataKegiatanWajibDetail($id)
    {
        $user = User::where('id', $id)->with('kelas', 'blok')->first();
        $today = date('Y-m-d');
        $paginationControl = 10;
        // data per hari ini
        $dataKegiatanUpacara = PresensiUpacara::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_upacaras.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginationControl);
        $dataKegiatanApel = PresensiApel::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_apels.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginationControl);
        $dataKegiatanSenam = PresensiSenam::with('jadwalKegiatanAsrama')
            ->where('user_id', $id)
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                $query->whereDate('tanggal_kegiatan', '<=', $today);
            })
            ->orderByDesc(
                JadwalKegiatanAsrama::select('tanggal_kegiatan')
                    ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_senams.jadwalKegiatanAsrama_id')
            )
            ->paginate($paginationControl);

        $dataKegiatanUpacara->each(function ($kegiatan) {
            $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
        });
        $dataKegiatanApel->each(function ($kegiatan) {
            $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
        });
        $dataKegiatanSenam->each(function ($kegiatan) {
            $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
        });

        $totalDataKegiatanUpacara = PresensiUpacara::where('user_id', $id)->count();
        $totalDataKegiatanApel = PresensiApel::where('user_id', $id)->count();
        $totalDataKegiatanSenam = PresensiSenam::where('user_id', $id)->count();

        $totalDataKegiatan = [
            'totalDataKegiatanUpacara' => $totalDataKegiatanUpacara,
            'totalDataKegiatanApel' => $totalDataKegiatanApel,
            'totalDataKegiatanSenam' => $totalDataKegiatanSenam,
        ];

        $keyPagination = key($totalDataKegiatan);

        if ($keyPagination === 'totalDataKegiatanUpacara') {
            $keyPagination = 'dataKegiatanUpacara';
        } elseif ($keyPagination === 'totalDataKegiatanApel') {
            $keyPagination = 'dataKegiatanApel';
        } elseif ($keyPagination === 'totalDataKegiatanSenam') {
            $keyPagination = 'dataKegiatanSenam';
        }

        $timeNow = now()->toTimeString();

        $rekapKegiatan = [
            'UPACARA' => [
                'Hadir' => $dataKegiatanUpacara->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanUpacara->where('status_kehadiran', 'Alpha')->count(),
                'Izin' => $dataKegiatanUpacara->where('status_kehadiran', 'Izin')->count(),
            ],
            'APEL' => [
                'Hadir' => $dataKegiatanApel->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanApel->where('status_kehadiran', 'Alpha')->count(),
                'Izin' => $dataKegiatanUpacara->where('status_kehadiran', 'Izin')->count(),
            ],
            'SENAM' => [
                'Hadir' => $dataKegiatanSenam->where('status_kehadiran', 'Hadir')->count(),
                'Alpha' => $dataKegiatanSenam->where('status_kehadiran', 'Alpha')->count(),
                'Izin' => $dataKegiatanUpacara->where('status_kehadiran', 'Izin')->count(),
            ]
        ];

        return view('admin.detail-absenkegiatan', compact('user', 'dataKegiatanUpacara', 'dataKegiatanApel', 'dataKegiatanSenam', 'timeNow', 'keyPagination', 'rekapKegiatan'));
    }

    public function dataKegiatanWajibDetailFilter(Request $request)
    {
        $data = $request->all();
        $paginationControl = 10;
        $today = date('Y-m-d');

        // Ambil data dari request
        $status_kehadiran = $data['status_kehadiran'] ?? null;
        $tanggal_kegiatan = $data['tanggal_kegiatan'] ?? null;
        $id = $data['user_idInput'];

        // dd($data);

        // Mulai query
        if (empty($status_kehadiran) && empty($tanggal_kegiatan)) {
            $dataKegiatanUpacara = $this->getDefaultData('PresensiUpacara', $id, $today, $paginationControl);
            $dataKegiatanApel = $this->getDefaultData('PresensiApel', $id, $today, $paginationControl);
            $dataKegiatanSenam = $this->getDefaultData('PresensiSenam', $id, $today, $paginationControl);

            $dataKegiatanUpacara->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });
            $dataKegiatanApel->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });
            $dataKegiatanSenam->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });

            $tableUpacara = view('admin.partials.data_kegiatan_upacara_table', compact('dataKegiatanUpacara'))->render();
            $tableApel = view('admin.partials.data_kegiatan_apel_table', compact('dataKegiatanApel'))->render();
            $tableSenam = view('admin.partials.data_kegiatan_senam_table', compact('dataKegiatanSenam'))->render();
        } else {
            $dataKegiatanUpacara = PresensiUpacara::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($status_kehadiran, $tanggal_kegiatan, $today) {
                    if (!empty($status_kehadiran)) {
                        $query->whereIn('status_kehadiran', $status_kehadiran);
                    }
                    if (!empty($tanggal_kegiatan)) {
                        $query->whereDate('tanggal_kegiatan', $tanggal_kegiatan);
                    }
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_upacaras.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);

            $dataKegiatanApel = PresensiApel::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($status_kehadiran, $tanggal_kegiatan, $today) {
                    if (!empty($status_kehadiran)) {
                        $query->whereIn('status_kehadiran', $status_kehadiran);
                    }
                    if (!empty($tanggal_kegiatan)) {
                        $query->whereDate('tanggal_kegiatan', $tanggal_kegiatan);
                    }
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_apels.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);

            $dataKegiatanSenam = PresensiSenam::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($status_kehadiran, $tanggal_kegiatan, $today) {
                    if (!empty($status_kehadiran)) {
                        $query->whereIn('status_kehadiran', $status_kehadiran);
                    }
                    if (!empty($tanggal_kegiatan)) {
                        $query->whereDate('tanggal_kegiatan', $tanggal_kegiatan);
                    }
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_senams.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);

            $dataKegiatanUpacara->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });
            $dataKegiatanApel->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });
            $dataKegiatanSenam->each(function ($kegiatan) {
                $kegiatan->formatted_date = Carbon::parse($kegiatan->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
            });

            $tableUpacara = view('admin.partials.data_kegiatan_upacara_table', compact('dataKegiatanUpacara'))->render();
            $tableApel = view('admin.partials.data_kegiatan_apel_table', compact('dataKegiatanApel'))->render();
            $tableSenam = view('admin.partials.data_kegiatan_senam_table', compact('dataKegiatanSenam'))->render();
        }

        return response()->json([
            'tableUpacara' => $tableUpacara,
            'tableApel' => $tableApel,
            'tableSenam' => $tableSenam,
        ]);
    }

    private function getDefaultData($model, $id, $today, $paginationControl)
    {
        if ($model === 'PresensiUpacara') {
            return PresensiUpacara::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_upacaras.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);
        } elseif ($model === 'PresensiApel') {
            return PresensiApel::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_apels.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);
        } elseif ($model === 'PresensiSenam') {
            return PresensiSenam::with('jadwalKegiatanAsrama')
                ->where('user_id', $id)
                ->whereHas('jadwalKegiatanAsrama', function ($query) use ($today) {
                    $query->whereDate('tanggal_kegiatan', '<=', $today);
                })
                ->orderByDesc(
                    JadwalKegiatanAsrama::select('tanggal_kegiatan')
                        ->whereColumn('jadwal_kegiatan_asramas.id', 'presensi_senams.jadwalKegiatanAsrama_id')
                )
                ->paginate($paginationControl);
        }
    }

    public function editDataKegiatanWajib(Request $request, $id)
    {
        try {
            $data = $request->all();

            $combinedData = [];
            $selectdb = '';

            if (isset($data['presensi_upacara_ids']) && $data['presensi_upacara_ids'] != null) {
                foreach ($data['presensi_upacara_ids'] as $index => $presensiId) {
                    $combinedData[] = [
                        'presensi_id' => $presensiId,
                        'status_kehadiran' => $data['status_kehadiran'][$index],
                    ];
                }
                $selectdb = 'presensi_upacaras';
            } elseif (isset($data['presensi_apel_ids']) && $data['presensi_apel_ids'] != null) {
                foreach ($data['presensi_apel_ids'] as $index => $presensiId) {
                    $combinedData[] = [
                        'presensi_id' => $presensiId,
                        'status_kehadiran' => $data['status_kehadiran'][$index],
                    ];
                }
                $selectdb = 'presensi_apels';
            } elseif (isset($data['presensi_senam_ids']) && $data['presensi_senam_ids'] != null) {
                foreach ($data['presensi_senam_ids'] as $index => $presensiId) {
                    $combinedData[] = [
                        'presensi_id' => $presensiId,
                        'status_kehadiran' => $data['status_kehadiran'][$index],
                    ];
                }
                $selectdb = 'presensi_senams';
            }
        } catch (\Throwable $th) {
            return redirect()->route('admin.dataKegiatanWajibDetail', $id)->with('error', $th->getMessage());
        }

        try {
            // Dapatkan data dari database
            $presensiData = $this->getDataFromDatabase($selectdb, $combinedData);

            // Bandingkan dan lakukan operasi update jika diperlukan
            $this->updateDataIfNeeded($presensiData, $combinedData, $selectdb);

            return redirect()->route('admin.dataKegiatanWajibDetail', $id)->with('success', 'Berhasil mengubah data kegiatan wajib');
        } catch (\Throwable $th) {
            return redirect()->route('admin.dataKegiatanWajibDetail', $id)->with('error', $th->getMessage());
        }
    }

    private function getDataFromDatabase($selectdb, $combinedData)
    {
        $presensiIds = array_column($combinedData, 'presensi_id');

        // Dapatkan data dari database berdasarkan $selectdb dan $presensiIds
        return DB::table($selectdb)
            ->whereIn('id', $presensiIds)
            ->orderBy(DB::raw('FIELD(id, ' . implode(',', $presensiIds) . ')'))
            ->get();
    }


    private function updateDataIfNeeded($presensiData, $combinedData, $selectdb)
    {
        foreach ($presensiData as $index => $data) {
            // dd($data, $combinedData[$index]['status_kehadiran']);
            // Bandingkan nilai status_kehadiran
            if ($data->status_kehadiran != $combinedData[$index]['status_kehadiran']) {
                // Lakukan operasi update di sini
                DB::table($selectdb)
                    ->where('id', $data->id)
                    ->update(['status_kehadiran' => $combinedData[$index]['status_kehadiran']]);
            }
        }
    }


    public function filteringEditJadwalKegiatanByBlok(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'tanggal_kegiatan' => 'required',
            'blok_id' => 'required',
            'jenis_kegiatan' => 'required',
            'checkBoxIDJadwalKegiatan' => 'nullable'
        ]);
        $dataFilterKegiatanByBlok = jadwalKegiatanAsrama::where('tanggal_kegiatan', $request->tanggal_kegiatan)
            ->Where('blok_id', $request->blok_id)
            ->Where('jenis_kegiatan', $request->jenis_kegiatan)
            ->get();
        // dd($dataFilterKegiatanByBlok);
        if ($dataFilterKegiatanByBlok->count() > 1) {
            // If there's more than one record, return the table view
            $table = view('admin.partials.table_filter_data_kegiatan_by_blok', compact('dataFilterKegiatanByBlok'))->render();
            return response()->json(['table' => $table]);
        } elseif ($dataFilterKegiatanByBlok->count() == 1) {
            // If there's only one record, return a custom JSON response
            $table = view('admin.partials.table_filter_data_kegiatan_by_blok', compact('dataFilterKegiatanByBlok'))->render();
            // return response()->json([]);
            return response()->json([
                'table' => $table
            ]);
        } else {
            // If no records are found
            return response()->json(['message' => 'No Data Found']);
        }
    }


    public function editJadwalKegiatanByBlok(Request $request)
    {
        try {
            $request->validate([
                'tanggal_kegiatan' => 'required',
                'blok_id' => 'required',
                'jenis_kegiatan' => 'required',
                'status_kehadiran' => 'required',
                'checkBoxIDJadwalKegiatan' => 'required'
            ]);
            // dd($request->all());
            $selectdb = '';
            if ($request->jenis_kegiatan == 'Apel') {
                $selectdb = 'presensi_apels';
            } elseif ($request->jenis_kegiatan == 'Upacara') {
                $selectdb = 'presensi_upacaras';
            } elseif ($request->jenis_kegiatan == 'Senam') {
                $selectdb = 'presensi_senams';
            }
            $getJadwal = jadwalKegiatanAsrama::where('tanggal_kegiatan', $request->tanggal_kegiatan)
                ->where('blok_id', $request->blok_id)
                ->where('jenis_kegiatan', $request->jenis_kegiatan)
                ->with('blokRuangan')
                ->get();

            if ($getJadwal->count() > 1) {
                $this->createCheckBoxEditJadwalKegiatanByBlok($request, $selectdb, $request->checkBoxIDJadwalKegiatan);
            } elseif ($getJadwal->count() == 1) {
                $this->updateDataPresensiByBlok($getJadwal, $request, $selectdb);
            } else {
                return redirect()->route('admin.jadwalKegiatanShow')->with('error', 'Tidak ada jadwal kegiatan pada blok ' . $getJadwal[0]->blokRuangan->name . ' pada tanggal ' . $request->tanggal_kegiatan);
            }
            return redirect()->route('admin.jadwalKegiatanShow')->with('success', 'Berhasil mengubah Status Kegiatan Asrama Untuk Siswa Pada Blok - ' . $getJadwal[0]->blokRuangan->name);
        } catch (\Throwable $th) {
            return redirect()->route('admin.jadwalKegiatanShow')->with('error', $th->getMessage());
            // return redirect()->route('admin.jadwalKegiatanShow')->with('error', 'Gagal mengubah Status Kegiatan Asrama Untuk Siswa');
        }
    }

    private function updateDataPresensiByBlok($getJadwal, $request, $selectdb)
    {
        foreach ($getJadwal as $jadwal) {
            DB::table($selectdb)->where('jadwalKegiatanAsrama_id', $jadwal->id)->update([
                'status_kehadiran' => $request->status_kehadiran,
            ]);
        }
    }
    private function createCheckBoxEditJadwalKegiatanByBlok($request, $selectdb, $checkBoxIDJadwalKegiatan)
    {
        $getJadwalHasMany = jadwalKegiatanAsrama::whereIn('id', $checkBoxIDJadwalKegiatan)
            ->where('blok_id', $request->blok_id)
            ->where('jenis_kegiatan', $request->jenis_kegiatan)
            ->with('blokRuangan')
            ->get();

        $this->updateDataPresensiByBlok($getJadwalHasMany, $request, $selectdb);
    }
}
