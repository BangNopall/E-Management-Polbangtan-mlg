<?php

namespace App\Http\Controllers;

use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use ZipArchive;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Kelas;

use App\Models\prodi;
use App\Models\Presence;
use App\Models\blokRuangan;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use App\Exports\laporanAbsen;
use App\Exports\laporanKegiatanWajib;
use App\Models\PresensiUpacara;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\JenisPelanggaran;
use Illuminate\Routing\Controller;
use App\Models\KategoriPelanggaran;
use App\Models\jadwalKegiatanAsrama;
use BaconQrCode\Renderer\Path\Move;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Illuminate\Support\Facades\Storage;

class GenerateReportController extends Controller
{
    public function index()
    {
        $blok = blokRuangan::all();

        $kelas = kelas::select('id', 'kelas')
            ->get()->groupBy('kelas');
        $prodi = prodi::select('id', 'prodi')
            ->get();
        $kegiatanAsrama = [
            'Upacara',
            'Apel',
            'Senam',
        ];

        // dd($kelas);

        $title = "Generate Report";

        return view('admin.generate', compact('blok', 'title', 'kelas', 'prodi', 'kegiatanAsrama'));
    }

    public function pdfReport(Request $request)
    {
        $request->validate([
            'tanggalSiswa' => 'required',
            'blok' => 'required',
        ]);

        // Mendeklarasi inputan request ke variabel
        $inputWeek = $request->input('tanggalSiswa');
        $blokId = $request->input('blok');

        // jika blokId adalah 1 maka blok adalah A
        if ($blokId == 1) {
            $blok = 'A';
        } elseif ($blokId == 2) {
            $blok = 'B';
        } elseif ($blokId == 3) {
            $blok = 'C';
        } elseif ($blokId == 4) {
            $blok = 'D';
        } elseif ($blokId == 5) {
            $blok = 'E';
        }

        $year = date('Y', strtotime($inputWeek . '-1')); // Tahun dari inputan tanggal
        $weekNumber = date('W', strtotime($inputWeek . '-1')); // Minggu dari inputan tanggal

        $startDate = date("Y-m-d", strtotime("{$year}-W{$weekNumber}-1")); // Hari pertama dalam minggu
        $endDate = date("Y-m-d", strtotime("{$startDate} +6 days"));   // Hari terakhir dalam minggu

        // Mencari data dari tabel presence berdasarkan startDate dan endDate serta berdasarkan user dari blok_ruangan_id yang dipilih
        $data = Presence::whereBetween('presence_date', [$startDate, $endDate])
            ->whereHas('user', function ($query) use ($blokId) {
                $query->where('blok_ruangan_id', $blokId);
            })
            ->with('user')
            ->with('user.kelas')
            ->with('user.blok')
            ->get();

        // Jika value submit sama dengan pdf
        if ($request->submit == 'pdf') {
            $pdf = Pdf::loadView('admin.generate.generate-pdf', compact('data', 'startDate', 'endDate', 'blok'));
            $storagePath = public_path('pdf');

            // Simpan file pdf
            $pdf->save($storagePath . '/BLOK_' . $blok . '_Laporan-Mingguan-EAbsen-Polbangtan-MLG.pdf');
            // Mengembalikan URL atau path ke file PDF yang baru disimpan
            $pdfFile = asset('pdf/BLOK_' . $blok . '_Laporan-Mingguan-EAbsen-Polbangtan-MLG.pdf');

            // Redirect
            return redirect($pdfFile);
        }

        // Jika value submit sama dengan excel, maka akan mengirim data, startDate, dan endDate tersebut ke exports laporanAbsen
        if ($request->submit == 'excel') {
            Excel::store(new laporanAbsen($data, $startDate, $endDate), 'BLOK_' . $blok . '_Laporan-Mingguan-EAbsen-Polbangtan-MLG.xlsx', 'publicnew', ExcelExcel::XLSX);

            // Mengembalikan URL atau path ke file Excel yang baru disimpan
            $path = asset('excel/BLOK_' . $blok . '_Laporan-Mingguan-EAbsen-Polbangtan-MLG.xlsx');

            // Mengunduh file dari public path
            return redirect($path);
        }
    }

    public function generateLaporanPelanggaran(Request $request)
    {
        $request->validate([
            'kelas' => 'required',
            'submit' => 'required',
            'prodi' => 'required',
        ]);

        // dd($request->all());
        $namaProdi = prodi::where('id', $request->prodi)->first('prodi')->prodi;
        $zip = new ZipArchive;
        $kelasPath = public_path('pelanggaran/pdf/kelas-' . $request->kelas . '-' . prodi::where('id', $request->prodi)->first('prodi')->prodi);
        $zipPath = $kelasPath . '/kelas-' . $request->kelas . '-' . $namaProdi . '_Laporan-Pelanggaran-Polbangtan-MLG' . '.zip';
        // Membuat folder jika belum ada
        if (!file_exists($kelasPath)) {
            mkdir($kelasPath, 0777, true);
        }

        $kelas = Kelas::where('kelas', $request->kelas)
            ->where('prodi_id', $request->prodi)
            ->get();

        $dataRequest = $this->processData($kelas);
        if ($dataRequest->every->isEmpty()) {
            return redirect()->route('admin.generate')->with('error', 'Data Pelanggaran Pada kelas tersebut tidak ditemukan.');
        }

        $raportData = [];
        foreach ($dataRequest as $data) {
            $data->each(function ($pelanggaran) use (&$raportData) {
                $raportData[] = $this->dataPelanggaranDetail($pelanggaran->user_id);
            });
        }
        // dd($namaProdi);
        if ($request->submit == 'pdf') {
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                foreach ($raportData as $raport) {
                    $data = $raport['data'];
                    $groupedData = $raport['groupedData'];
                    $groupedDataraport = $raport['groupedDataraport'];

                    $pdfPath = $kelasPath . '/' . $raport['data'][0]['user']['kelas']['nama_kelas'] . '_' . $raport['data'][0]['user']['name'] . '_Laporan-Pelanggaran-Polbangtan-MLG.pdf';

                    // Load view dan simpan ke file PDF
                    $pdf = Pdf::loadView('admin.generate.generate-pelanggaran', compact('data', 'groupedData', 'groupedDataraport'))->setPaper('a4', 'portrait');
                    $pdf->save($pdfPath);

                    // Tambahkan file PDF ke dalam zip
                    $zip->addFile($pdfPath, basename($pdfPath));
                }

                $zip->close();

                // Hapus file PDF setelah ZIP diunduh
                foreach ($raportData as $raport) {
                    $pdfPath = $kelasPath . '/' . $raport['data'][0]['user']['kelas']['nama_kelas'] . '_' . $raport['data'][0]['user']['name'] . '_Laporan-Pelanggaran-Polbangtan-MLG.pdf';
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                }
                // Berikan link ke file zip untuk diunduh
                return response()->download($zipPath)->deleteFileAfterSend();
            } else {
                return redirect()->route('admin.generate')->with('error', 'Gagal membuat file zip.');
            }
        } else {
            return redirect()->route('admin.generate')->with('error', 'Data Request Tidak valid.');
        }
    }

    private function processData($kelas)
    {
        $data = [];
        foreach ($kelas as $k) {
            $user = User::where('kelas_id', $k->id)
                ->select('id')
                ->get();
            if (!$user->isEmpty()) {
                $data[] = $user->pluck('id')->toArray();
            }
        }

        // dd($data);

        if ($kelas === null) {
            return redirect()->route('admin.generate')->with('error', 'Data Pelanggaran kelas tersebut tidak ditemukan.');
        }

        $pelanggaranData = collect($data)->map(function ($userIds) {
            return Pelanggaran::whereIn('user_id', $userIds)
                ->whereIn('statusPelanggaran', ['progressing', 'Done'])
                ->select('id', 'user_id', 'jenis_pelanggaran_id')
                ->get();
        });

        // dd($pelanggaranData);

        // dd($pelanggaranData);

        return collect($pelanggaranData);
    }

    private function dataPelanggaranDetail($id)
    {
        // dd($id);
        $data = Pelanggaran::with('user')
            ->where('user_id', $id)
            ->whereIn('statusPelanggaran', ['Done', 'progressing'])
            ->select('id', 'user_id', 'jenis_pelanggaran_id')
            ->get();
        if ($data->isEmpty()) {
            return redirect()->back()->with('error', 'Data pelanggaran tidak ditemukan.');
        }

        $totalPelanggaran = $data->count();

        $data->each(function ($pelanggaran) use ($totalPelanggaran) {
            $blok = BlokRuangan::where('id', $pelanggaran->user->blok_ruangan_id)
                ->select('name')
                ->first();
            $pelanggaran->blok = $blok->name;
        });

        $groupedData = [];
        $data->each(function ($pelanggaran) use (&$groupedData) {
            $jenisPelanggaran = JenisPelanggaran::where('id', $pelanggaran->jenis_pelanggaran_id)
                ->select('jenis_pelanggaran', 'kategori_id', 'poin', 'sub_kategori')
                ->first();
            $kategoriPelanggaran = KategoriPelanggaran::where('id', $jenisPelanggaran->kategori_id)
                ->select('name')
                ->first();
            $azKey = chr(65 + $jenisPelanggaran->kategori_id - 1);

            $groupedData[$azKey][$kategoriPelanggaran->name][] = [
                'AZ' => $azKey,
                'kategori_pelanggaran' => $kategoriPelanggaran->name,
                'jenis_pelanggaran' => $jenisPelanggaran->jenis_pelanggaran,
                'poin' => $jenisPelanggaran->poin,
                'sub_kategori' => $jenisPelanggaran->sub_kategori,
            ];
        });
        ksort($groupedData);

        $groupedDataraport = [];
        $totalPoin = 0;
        $allCategories = KategoriPelanggaran::pluck('name')->toArray();
        $i = 65;
        foreach ($allCategories as $categoryName) {
            $azKey = chr($i);
            $i++;
            $categoryKey = $azKey . "." . " " . $categoryName;
            $groupedDataraport[$categoryKey] = [
                'name' => $categoryName,
                'totalPoinKategori' => 0,
            ];
        }
        // Sortir data berdasarkan kategori
        ksort($groupedDataraport);
        // Process the data
        $data->each(function ($pelanggaran) use (&$groupedDataraport, &$totalPoin) {
            $jenisPelanggaran = JenisPelanggaran::where('id', $pelanggaran->jenis_pelanggaran_id)
                ->select('kategori_id', 'poin')
                ->first();
            $kategoriPelanggaran = KategoriPelanggaran::where('id', $jenisPelanggaran->kategori_id)
                ->select('name')
                ->first();
            $azKey = chr(65 + $jenisPelanggaran->kategori_id - 1);
            $poin = $jenisPelanggaran->poin;
            $categoryKey = $azKey . "." . " " . $kategoriPelanggaran->name;
            if (!isset($groupedDataraport[$categoryKey])) {
                $groupedDataraport[$categoryKey] = [
                    'totalPoinKategori' => 0,
                ];
            }
            $groupedDataraport[$categoryKey]['totalPoinKategori'] += $poin;
            $totalPoin += $poin;
        });
        foreach ($groupedDataraport as $categoryKey => &$categoryData) {
            if ($categoryKey !== 'totalPoin') {
                if (!isset($categoryData['totalPoinKategori'])) {
                    $categoryData['totalPoinKategori'] = 0;
                }
            }
        }
        $groupedDataraport['totalPoin'] = $totalPoin;

        // dd($groupedDataraport);
        // return view('admin.admin-edit.detail-datapelanggaran', compact('title', 'data', 'groupedData', 'groupedDataraport'));

        return [
            'data' => $data,
            'groupedData' => $groupedData,
            'groupedDataraport' => $groupedDataraport,
        ];
    }

    public function generateLaporanPelanggaranKegiatanAsrama(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required',
                'jenis_kegiatan' => 'required',
                'blok_id' => 'required',
            ]);
            $startDate = null;
            $endDate = null;

            if ($request->type == "bulanan") {
                $request->validate([
                    'monthlyInput' => 'required',
                ]);
                $startDate = Carbon::parse($request->monthlyInput)->startOfMonth();
                $endDate = Carbon::parse($request->monthlyInput)->endOfMonth();
            }

            if ($request->type == "mingguan") {
                $request->validate([
                    'weeklyInput' => 'required',
                ]);
                $startDate = Carbon::parse($request->weeklyInput)->startOfWeek();
                $endDate = Carbon::parse($request->weeklyInput)->endOfWeek();
            }
            $dateRangeText = $startDate->translatedFormat('d F Y') . ' - ' . $endDate->translatedFormat('d F Y');

            $tanggalkegiatan = [];

            while ($startDate->lte($endDate)) {
                $tanggalkegiatan[] = $startDate->copy()->translatedFormat('d F Y');
                $startDate->addDay()->translatedFormat('d F Y'); // Tambah 1 hari ke startDate
            }

            if ($request->jenis_kegiatan == "Upacara") {
                $getPresensiUpacaraAllData = $this->generateLaporanUpacara($request, $dateRangeText);
                $zip = new ZipArchive();
                $blokName = blokRuangan::where('id', $request->blok_id)->first()->name;
                $zipFileName = 'kegiatan-asrama_' . $request->jenis_kegiatan . '_Blok_' . $blokName . '_Kegiatan_Wajib_Upacara_BLOK-' . $blokName . '-' . $dateRangeText . '.zip';
                $directoryPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                $zipFilePath = $directoryPath . '/' . $zipFileName;

                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0777, true);
                }

                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $dataExcel = $getPresensiUpacaraAllData;
                    $excelname = 'BLOK_' . $blokName . '_Laporan-kegiatan-wajib-Polbangtan-MLG.xlsx';
                    $excelallpath = $request->jenis_kegiatan . '/Blok ' . $blokName . '/' . $excelname;
                    $localExcelPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                    $requestKegiatanUser = $request->jenis_kegiatan;

                    Excel::store(new laporanKegiatanWajib($dataExcel, $dateRangeText, $tanggalkegiatan, $blokName, $requestKegiatanUser), $excelallpath, 'publiclaporankegiatan', ExcelExcel::XLSX);

                    $zip->addFile($localExcelPath . '/' . $excelname, $excelname);


                    foreach ($getPresensiUpacaraAllData as $user_id => $getPresensiUpacara) {
                        $username = $getPresensiUpacara->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiUpacara->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;

                        // Generate PDF
                        $pdf = Pdf::loadView('admin.generate.generate-upacara', compact('getPresensiUpacara', 'dateRangeText'))->setPaper('a4', 'portrait');

                        // Save PDF file
                        $pdf->save($pdfFilePath);

                        // Ensure the PDF file exists before adding it to the zip
                        if (file_exists($pdfFilePath)) {
                            $zip->addFile($pdfFilePath, $pdfFileName);
                        }
                        // dd($pdf);
                    }

                    // Close the ZipArchive
                    $zip->close();

                    foreach ($getPresensiUpacaraAllData as $user_id => $getPresensiUpacara) {
                        $username = $getPresensiUpacara->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiUpacara->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;
                        if (file_exists($pdfFilePath)) {
                            unlink($pdfFilePath);
                        }
                    }

                    if(file_exists($localExcelPath . '/' . $excelname)){
                        unlink($localExcelPath . '/' . $excelname);
                    }

                    // Provide download link to the user
                    return response()->download($zipFilePath)->deleteFileAfterSend();
                } else {
                    // Handle error if zip file couldn't be created
                    return response()->json(['error' => 'Unable to create zip file.']);
                }
            }

            if ($request->jenis_kegiatan == "Apel") {
                $getPresensiApelAllData = $this->generateLaporanApel($request, $dateRangeText);
                $zip = new ZipArchive();
                $blokName = blokRuangan::where('id', $request->blok_id)->first()->name;
                $zipFileName = 'kegiatan-asrama_' . $request->jenis_kegiatan . '_Blok_' . $blokName . '_Kegiatan_Wajib_Apel_BLOK-' . $blokName . '-' . $dateRangeText . '.zip';
                $directoryPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                $zipFilePath = $directoryPath . '/' . $zipFileName;

                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0777, true);
                }

                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $dataExcel = $getPresensiApelAllData;
                    $excelname = 'BLOK_' . $blokName . '_Laporan-kegiatan-wajib-Polbangtan-MLG.xlsx';
                    $excelallpath = $request->jenis_kegiatan . '/Blok ' . $blokName . '/' . $excelname;
                    $localExcelPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                    $requestKegiatanUser = $request->jenis_kegiatan;

                    Excel::store(new laporanKegiatanWajib($dataExcel, $dateRangeText, $tanggalkegiatan, $blokName, $requestKegiatanUser), $excelallpath, 'publiclaporankegiatan', ExcelExcel::XLSX);

                    $zip->addFile($localExcelPath . '/' . $excelname, $excelname);
                    foreach ($getPresensiApelAllData as $user_id => $getPresensiApel) {
                        $username = $getPresensiApel->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiApel->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;

                        $pdf = Pdf::loadView('admin.generate.generate-apel', compact('getPresensiApel', 'dateRangeText'))->setPaper('a4', 'portrait');

                        $pdf->save($pdfFilePath);

                        if (file_exists($pdfFilePath)) {
                            $zip->addFile($pdfFilePath, $pdfFileName);
                        }
                    }
                    $zip->close();

                    // MOTONG DISINI UNTUK LAPORAN KE PIMPINAN

                    foreach ($getPresensiApelAllData as $user_id => $getPresensiApel) {
                        $username = $getPresensiApel->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiApel->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;
                        if (file_exists($pdfFilePath)) {
                            unlink($pdfFilePath);
                        }
                    }

                    if(file_exists($localExcelPath . '/' . $excelname)){
                        unlink($localExcelPath . '/' . $excelname);
                    }

                    // Provide download link to the user
                    return response()->download($zipFilePath)->deleteFileAfterSend();
                } else {
                    // Handle error if zip file couldn't be created
                    return response()->json(['error' => 'Unable to create zip file.']);
                }
            }
            if ($request->jenis_kegiatan == "Senam") {
                $getPresensiSenamAllData = $this->generateLaporanSenam($request, $dateRangeText);
                $zip = new ZipArchive();
                $blokName = blokRuangan::where('id', $request->blok_id)->first()->name;
                $zipFileName = 'kegiatan-asrama_' . $request->jenis_kegiatan . '_Blok_' . $blokName . '_Kegiatan_Wajib_Senam_BLOK-' . $blokName . '-' . $dateRangeText . '.zip';
                $directoryPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                $zipFilePath = $directoryPath . '/' . $zipFileName;

                if (!file_exists($directoryPath)) {
                    mkdir($directoryPath, 0777, true);
                }

                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $dataExcel = $getPresensiSenamAllData;
                    $excelname = 'BLOK_' . $blokName . '_Laporan-kegiatan-wajib-Polbangtan-MLG.xlsx';
                    $excelallpath = $request->jenis_kegiatan . '/Blok ' . $blokName . '/' . $excelname;
                    $localExcelPath = public_path('kegiatan-asrama/' . $request->jenis_kegiatan . '/Blok ' . $blokName);
                    $requestKegiatanUser = $request->jenis_kegiatan;

                    Excel::store(new laporanKegiatanWajib($dataExcel, $dateRangeText, $tanggalkegiatan, $blokName, $requestKegiatanUser), $excelallpath, 'publiclaporankegiatan', ExcelExcel::XLSX);

                    $zip->addFile($localExcelPath . '/' . $excelname, $excelname);
                    foreach ($getPresensiSenamAllData as $user_id => $getPresensiSenam) {
                        $username = $getPresensiSenam->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiSenam->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;

                        // Generate PDF
                        $pdf = Pdf::loadView('admin.generate.generate-senam', compact('getPresensiSenam', 'dateRangeText'))->setPaper('a4', 'portrait');

                        // Save PDF file
                        $pdf->save($pdfFilePath);

                        // Ensure the PDF file exists before adding it to the zip
                        if (file_exists($pdfFilePath)) {
                            $zip->addFile($pdfFilePath, $pdfFileName);
                        }
                        // dd($pdf);
                    }

                    // Close the ZipArchive
                    $zip->close();

                    foreach ($getPresensiSenamAllData as $user_id => $getPresensiSenam) {
                        $username = $getPresensiSenam->first()->user->name;
                        $pdfFileName = 'BLOK_' . $getPresensiSenam->first()->user->blok->name . '_' . $username . '-' . $request->jenis_kegiatan . '-Polbangtan-MLG.pdf';
                        $pdfFilePath = $directoryPath . '/' . $pdfFileName;
                        if (file_exists($pdfFilePath)) {
                            unlink($pdfFilePath);
                        }
                    }

                    if(file_exists($localExcelPath . '/' . $excelname)){
                        unlink($localExcelPath . '/' . $excelname);
                    }

                    // Provide download link to the user
                    return response()->download($zipFilePath)->deleteFileAfterSend();
                } else {
                    // Handle error if zip file couldn't be created
                    return response()->json(['error' => 'Unable to create zip file.']);
                }
            }
            return view('admin.generate.generate-upacara', compact('getPresensiUpacara', 'dateRangeText'));
        } catch (\Throwable $th) {
            // return redirect()->route('admin.generate')->with('error', 'Data Report Jadwal Kegiatan Wajib Tidak Ditemukan.');
            return redirect()->route('admin.generate')->with('error', $th->getMessage());
        }
    }

    private function generateLaporanUpacara($request, $dateRangeText)
    {
        $getPresensiUpacara = PresensiUpacara::with('user', 'user.kelas', 'user.blok', 'user.prodi', 'jadwalKegiatanAsrama')
            ->whereHas('user', function ($query) use ($request) {
                $query->where('blok_ruangan_id', $request->blok_id);
                $query->where('role_id', 3);
            })
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($request) {
                $query->where('jenis_kegiatan', $request->jenis_kegiatan);

                if ($request->type == "bulanan") {
                    // Jika jenis kegiatan bulanan, tambahkan filter berdasarkan bulan yang dipilih
                    $query->whereMonth('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->month);
                    $query->whereYear('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->year);
                }

                if ($request->type == "mingguan") {
                    // Jika jenis kegiatan mingguan, tambahkan filter berdasarkan minggu yang dipilih
                    $query->where('tanggal_kegiatan', '>=', Carbon::parse($request->weeklyInput)->startOfWeek())
                        ->where('tanggal_kegiatan', '<=', Carbon::parse($request->weeklyInput)->endOfWeek());
                }
            })
            ->get()
            ->sortByDesc(function ($item) {
                return $item->jadwalKegiatanAsrama->tanggal_kegiatan;
            })
            ->groupBy('user_id')
            ->map(function ($group) {
                $totalHadir = $group->where('status_kehadiran', 'Hadir')->count();
                $totalAlpha = $group->where('status_kehadiran', 'Alpha')->count();
                $totalIzin = $group->where('status_kehadiran', 'Izin')->count();
                return $group->map(function ($item) use ($totalAlpha, $totalHadir, $totalIzin) {
                    $item->formatted_date = Carbon::parse($item->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
                    $item->formatted_mulai_acara = Carbon::parse($item->jadwalKegiatanAsrama->mulai_acara)->format('H:i');
                    $item->formatted_selesai_acara = Carbon::parse($item->jadwalKegiatanAsrama->selesai_acara)->format('H:i');
                    $item->formatted_jam_kehadiran = Carbon::parse($item->jam_kehadiran)->format('H:i');
                    $item->total_hadir = $totalHadir;
                    $item->total_alpha = $totalAlpha;
                    $item->total_izin = $totalIzin;
                    return $item;
                });
            });
        // dd($getPresensiUpacara);
        return $getPresensiUpacara;
    }
    private function generateLaporanApel($request, $dateRangeText)
    {
        $getPresensiApel = PresensiApel::with('user', 'user.kelas', 'user.blok', 'user.prodi', 'jadwalKegiatanAsrama')
            ->whereHas('user', function ($query) use ($request) {
                $query->where('blok_ruangan_id', $request->blok_id);
                $query->where('role_id', 3);
            })
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($request) {
                $query->where('jenis_kegiatan', $request->jenis_kegiatan);

                if ($request->type == "bulanan") {
                    // Jika jenis kegiatan bulanan, tambahkan filter berdasarkan bulan yang dipilih
                    $query->whereMonth('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->month);
                    $query->whereYear('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->year);
                }

                if ($request->type == "mingguan") {
                    // Jika jenis kegiatan mingguan, tambahkan filter berdasarkan minggu yang dipilih
                    $query->where('tanggal_kegiatan', '>=', Carbon::parse($request->weeklyInput)->startOfWeek())
                        ->where('tanggal_kegiatan', '<=', Carbon::parse($request->weeklyInput)->endOfWeek());
                }
            })
            ->get()
            ->sortByDesc(function ($item) {
                return $item->jadwalKegiatanAsrama->tanggal_kegiatan;
            })
            ->groupBy('user_id')
            ->map(function ($group) {
                $totalHadir = $group->where('status_kehadiran', 'Hadir')->count();
                $totalAlpha = $group->where('status_kehadiran', 'Alpha')->count();
                $totalIzin = $group->where('status_kehadiran', 'Izin')->count();
                return $group->map(function ($item) use ($totalAlpha, $totalHadir, $totalIzin) {
                    $item->formatted_date = Carbon::parse($item->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
                    $item->formatted_mulai_acara = Carbon::parse($item->jadwalKegiatanAsrama->mulai_acara)->format('H:i');
                    $item->formatted_selesai_acara = Carbon::parse($item->jadwalKegiatanAsrama->selesai_acara)->format('H:i');
                    $item->formatted_jam_kehadiran = Carbon::parse($item->jam_kehadiran)->format('H:i');
                    $item->total_hadir = $totalHadir;
                    $item->total_alpha = $totalAlpha;
                    $item->total_izin = $totalIzin;
                    return $item;
                });
            });
        return $getPresensiApel;
    }

    private function generateLaporanSenam($request, $dateRangeText)
    {
        $getPresensiSenam = PresensiSenam::with('user', 'user.kelas', 'user.blok', 'user.prodi', 'jadwalKegiatanAsrama')
            ->whereHas('user', function ($query) use ($request) {
                $query->where('blok_ruangan_id', $request->blok_id);
                $query->where('role_id', 3);
            })
            ->whereHas('jadwalKegiatanAsrama', function ($query) use ($request) {
                $query->where('jenis_kegiatan', $request->jenis_kegiatan);

                if ($request->type == "bulanan") {
                    // Jika jenis kegiatan bulanan, tambahkan filter berdasarkan bulan yang dipilih
                    $query->whereMonth('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->month);
                    $query->whereYear('tanggal_kegiatan', Carbon::parse($request->monthlyInput)->year);
                }

                if ($request->type == "mingguan") {
                    // Jika jenis kegiatan mingguan, tambahkan filter berdasarkan minggu yang dipilih
                    $query->where('tanggal_kegiatan', '>=', Carbon::parse($request->weeklyInput)->startOfWeek())
                        ->where('tanggal_kegiatan', '<=', Carbon::parse($request->weeklyInput)->endOfWeek());
                }
            })
            ->get()
            ->sortByDesc(function ($item) {
                return $item->jadwalKegiatanAsrama->tanggal_kegiatan;
            })
            ->groupBy('user_id')
            ->map(function ($group) {
                $totalHadir = $group->where('status_kehadiran', 'Hadir')->count();
                $totalAlpha = $group->where('status_kehadiran', 'Alpha')->count();
                $totalIzin = $group->where('status_kehadiran', 'Izin')->count();
                return $group->map(function ($item) use ($totalAlpha, $totalHadir, $totalIzin) {
                    $item->formatted_date = Carbon::parse($item->jadwalKegiatanAsrama->tanggal_kegiatan)->format('d F Y');
                    $item->formatted_mulai_acara = Carbon::parse($item->jadwalKegiatanAsrama->mulai_acara)->format('H:i');
                    $item->formatted_selesai_acara = Carbon::parse($item->jadwalKegiatanAsrama->selesai_acara)->format('H:i');
                    $item->formatted_jam_kehadiran = Carbon::parse($item->jam_kehadiran)->format('H:i');
                    $item->total_hadir = $totalHadir;
                    $item->total_alpha = $totalAlpha;
                    $item->total_izin = $totalIzin;
                    return $item;
                });
            });
        return $getPresensiSenam;
    }
}
