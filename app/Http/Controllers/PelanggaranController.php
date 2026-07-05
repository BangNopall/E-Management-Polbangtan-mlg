<?php

namespace App\Http\Controllers;

use LDAP\Result;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Kelas;
use App\Models\blokRuangan;
use App\Models\Pelanggaran;
use Illuminate\Http\Request;
use App\Models\JenisPelanggaran;
use App\Models\KategoriPelanggaran;
use App\Http\Controllers\Controller;
use Doctrine\DBAL\Query;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class PelanggaranController extends Controller
{
    public function formHukumShow($kategori_uuid)
    {
        $validasiUser = Pelanggaran::where('user_id', Auth::user()->id)
            ->where('statusPelanggaran', 'scanned')
            ->orWhere('statusPelanggaran', 'rejected')
            ->first();
        if (!$validasiUser) {
            return redirect()->route('home.riwayatPelanggaran')->with('error', 'Dilarang mengakses halaman ini Diluar izin');
        } else {
            $title = "Form Hukum";
            $kategoriPelanggaran = KategoriPelanggaran::select('id', 'name')->get();
            $oneKategoriPelanggaran = KategoriPelanggaran::where('id', $kategori_uuid)->select('id', 'name')->get();

            $i = 65; // ASCII value for 'A'
            foreach ($kategoriPelanggaran as $kategori) {
                $kategori->AZ = chr($i);
                $i++;
            }

            $jenisPelanggaran = JenisPelanggaran::select('id', 'kategori_id', 'jenis_pelanggaran', 'poin', 'sub_kategori')->get();

            // Group jenisPelanggaran based on kategori_id
            $jenisPelanggaranGrouped = $jenisPelanggaran->groupBy('kategori_id');
            $user_id = Auth::user()->id;

            return view('formhukum', compact('title', 'kategoriPelanggaran', 'jenisPelanggaranGrouped', 'oneKategoriPelanggaran', 'user_id'));
        }
    }

    public function formHukumKategoriSubmit(Request $request, $user_id)
    {
        $request->validate([
            'jenis_pelanggaran' => 'required|array',
        ]);

        foreach ($request->jenis_pelanggaran as $jenis) {
            $id_user = Auth::user()->id;

            $dataPelanggaran = Pelanggaran::where('user_id', $id_user)
                ->where(function ($query) {
                    $query->where('statusPelanggaran', 'scanned')
                        ->orWhere('statusPelanggaran', 'rejected');
                })
                ->first();

            if ($dataPelanggaran) {
                $oldStatus = $dataPelanggaran->statusPelanggaran;
                $dataPelanggaran->jenis_pelanggaran_id = $jenis;
                $dataPelanggaran->statusPelanggaran = 'submitted';
                $dataPelanggaran->save();
                if ($oldStatus == 'rejected') {
                    return redirect()->route('home.riwayatPelanggaran')->with('success', 'Pelanggaran Anda berhasil diubah');
                } elseif ($oldStatus == 'scanned') {
                    return redirect()->route('home.qrhukum')->with('success', 'Pelanggaran Anda berhasil ditambahkan');
                }
            } else {
                return redirect()->route('home.qrhukum')->with('error', 'Form Tidak Valid');
            }
        }
    }

    public function riwayatPelanggaran()
    {
        $title = 'Riwayat Pelanggaran';
        $id_user = Auth::user()->id;

        // Query for non-rejected data
        $data = Pelanggaran::where('user_id', $id_user)
            ->whereIn('statusPelanggaran', ['submitted', 'progressing', 'Done'])
            ->orderByRaw("FIELD(statusPelanggaran, 'submitted', 'progressing', 'Done')")
            ->paginate(10);

        // Query for rejected data
        $dataRejected = Pelanggaran::where('user_id', $id_user)
            ->where('statusPelanggaran', 'rejected')
            ->paginate(10);

        // Modify the items in the paginated result
        $data->each(function ($pelanggaran) {
            $pelanggaran->formatted_date = Carbon::parse($pelanggaran->date)->format('d F Y');
            $jenisPelanggaran = JenisPelanggaran::where('id', $pelanggaran->jenis_pelanggaran_id)
                ->select('jenis_pelanggaran', 'kategori_id', 'poin', 'sub_kategori')
                ->first();

            if ($jenisPelanggaran) {
                $pelanggaran->jenis_pelanggaran = $jenisPelanggaran->jenis_pelanggaran;
            }
        });

        // dd($data);

        $dataRejected->each(function ($pelanggaran) {
            $pelanggaran->formatted_date = Carbon::parse($pelanggaran->date)->format('d F Y');
            $jenisPelanggaran = JenisPelanggaran::where('id', $pelanggaran->jenis_pelanggaran_id)
                ->select('jenis_pelanggaran', 'kategori_id', 'poin', 'sub_kategori')
                ->first();

            if ($jenisPelanggaran) {
                $pelanggaran->jenis_pelanggaran = $jenisPelanggaran->jenis_pelanggaran;
            }
        });

        // dd($data);

        return view('riwayat-pelanggaran', compact('title', 'data', 'dataRejected'));
    }

    public function riwayatPelanggaranDetail($id)
    {
        try {
            $title = 'Detail Pelanggaran';
            $data = Pelanggaran::where('id', $id)->first();
            if ($data->statusPelanggaran == 'submitted') {
                return redirect()->route('home.riwayatPelanggaran')->with('error', 'Pelanggaran belum di proses');
            }
            $jenisPelanggaran = JenisPelanggaran::where('id', $data->jenis_pelanggaran_id)
                ->select('jenis_pelanggaran', 'kategori_id', 'poin', 'sub_kategori')
                ->first();
            $kategoriPelanggaran = KategoriPelanggaran::where('id', $jenisPelanggaran->kategori_id)
                ->select('name')
                ->first();
            $accDokumen = User::where('id', $data->accepted_id)
                ->select('name', 'role_id')
                ->first();
            if ($accDokumen->role_id == 1) {
                $data->accDokumenRole = 'Admin';
            } elseif ($accDokumen->role_id == 4) {
                $data->accDokumenRole = 'Pelatih';
            }
            $data->AZ = chr(65 + $jenisPelanggaran->kategori_id - 1);
            $data->jenis_pelanggaran = $jenisPelanggaran->jenis_pelanggaran;
            $data->poin = $jenisPelanggaran->poin;
            $data->sub_kategori = $jenisPelanggaran->sub_kategori;
            $data->kategori_pelanggaran = $kategoriPelanggaran->name;
            $data->accDokumenName = $accDokumen->name;
            // dd($data);
            return view('detail-pelanggaran', compact('title', 'data'));
        } catch (\Exception $e) {
            return redirect()->route('home.riwayatPelanggaran')->with('error', 'Pelanggaran tidak ditemukan');
        }
    }

    public function dataPelanggaran()
    {
        $title = 'Data Pelanggaran';

        // Get data pelanggaran
        $data = Pelanggaran::with('user')
            ->whereIn('statusPelanggaran', ['progressing', 'Done'])
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');
        $data = $data->map(function ($pelanggaranByUser) {
            $user_id = $pelanggaranByUser->first()->user_id;
            $totalPelanggaran = Pelanggaran::where('user_id', $user_id)
                ->whereIn('statusPelanggaran', ['progressing', 'Done'])
                ->count();
            $pelanggaranByUser[0]['total_pelanggaran'] = $totalPelanggaran;

            $totalPelanggaranPoin = Pelanggaran::where('user_id', $user_id)
                ->whereIn('statusPelanggaran', ['progressing', 'Done'])
                ->select('id', 'user_id', 'jenis_pelanggaran_id')
                ->get();
            $defaultPoin = 0;
            foreach ($totalPelanggaranPoin as $pelanggaran) {
                $jenisPelanggaran = JenisPelanggaran::where('id', $pelanggaran->jenis_pelanggaran_id)
                    ->select('poin')
                    ->first();
                $defaultPoin += $jenisPelanggaran->poin;
            }
            $pelanggaranByUser['total_poin'] = $defaultPoin;
            return $pelanggaranByUser;
        })->sortByDesc(function ($pelanggaranByUser) {
            return $pelanggaranByUser['total_poin'];
        });

        // pagination
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $data = new LengthAwarePaginator(
            $data->forPage($currentPage, 10),
            $data->count(),
            10,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        // dd($data);
        return view('admin.data-pelanggaran', compact('title', 'data'));
    }

    public function searchDataPelanggaran(Request $request)
    {
        $title = 'Data Pelanggaran';
        $search = $request->searchInput;

        $resultsData = Pelanggaran::with('user.kelas')
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nim', 'like', '%' . $search . '%')
                    ->orWhereHas('kelas', function ($kelasQuery) use ($search) {
                        $kelasQuery->where('nama_kelas', 'like', '%' . $search . '%');
                    });
            })
            ->whereIn('statusPelanggaran', ['progressing', 'Done'])
            ->orderBy('user_id')
            ->get()
            ->groupBy('user_id');

        // mapping data total pelanggaran
        $totalPelanggaranData = $resultsData->map(function ($pelanggaranByUser) {
            $firstPelanggaran = $pelanggaranByUser->first();
            if ($firstPelanggaran) {
                return $firstPelanggaran->user_id;
            }

            return null;
        });
        // tambahin data pelanggaran ke data utama
        $resultsData = $resultsData->map(function ($pelanggaranByUser) {
            $user_id = $pelanggaranByUser->first()->user_id;

            // Hitung total pelanggaran untuk setiap user
            $totalPelanggaran = Pelanggaran::where('user_id', $user_id)
                ->whereIn('statusPelanggaran', ['progressing', 'Done'])
                ->count();

            // Tambahkan total pelanggaran ke data
            $pelanggaranByUser[0]['total_pelanggaran'] = $totalPelanggaran;

            return $pelanggaranByUser;
        });

        // dd($resultsData);
        return response()->json($resultsData);
        // return view('admin.data-pelanggaran', compact('title', 'data'));
    }

    public function dataPelanggaranDetail($id)
    {
        $title = 'Detail Pelanggaran';
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
        return view('admin.admin-edit.detail-datapelanggaran', compact('title', 'data', 'groupedData', 'groupedDataraport'));
    }

    public function editKategoriStore(Request $request)
    {
        try {
            $kategoriPelanggaran = KategoriPelanggaran::all();
            $submittedCategories = $request->all();

            // Identify the changed categories and find their IDs
            $changedCategoryIds = [];
            foreach ($kategoriPelanggaran as $kategori) {
                $submittedName = $submittedCategories[$kategori->id] ?? null;
                if ($submittedName !== $kategori->name) {
                    $changedCategoryIds[] = $kategori->id;
                }
            }

            if (count($changedCategoryIds) > 0) {
                foreach ($changedCategoryIds as $categoryId) {
                    $submittedName = $submittedCategories[$categoryId] ?? null;
                    KategoriPelanggaran::where('id', $categoryId)
                        ->update(['name' => $submittedName]);
                }

                // Redirect or perform any other actions after updating
                return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('success', 'Data pelanggaran berhasil diperbarui');
            } else {
                // No categories were changed.
                return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('error', 'Tidak ada perubahan pada data pelanggaran');
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('error', 'Terjadi kesalahan saat pembaruan data pelanggaran: ' . $e->getMessage());
        }
    }

    public function createKategori(Request $request)
    {
        try {
            $request->validate([
                'nameKategori' => 'required'
            ]);

            $kategoriPelanggaran = new KategoriPelanggaran();
            $kategoriPelanggaran->name = $request->nameKategori;
            $kategoriPelanggaran->save();
            return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('success', 'Kategori Baru berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('error', 'Kategori Baru tidak boleh kosong.');
        }
    }

    public function createJenisPelanggaran(Request $request, $id)
    {
        try {
            $request->validate([
                'jenis_pelanggaran' => 'required',
            ]);
            $jenisPelanggaran = new JenisPelanggaran();
            $jenisPelanggaran->kategori_id = $id;
            $jenisPelanggaran->jenis_pelanggaran = $request->jenis_pelanggaran;
            $jenisPelanggaran->poin = 0;
            $jenisPelanggaran->save();
            // dd($jenisPelanggaran);
            return redirect()->route('admin.editPelanggaranIdKategori', $id)->with('success', 'Jenis Pelanggaran Baru berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', $id)->with('error', 'Jenis Pelanggaran Baru tidak boleh kosong.');
        }
    }

    private function getJenisPelanggaran($kategoriId)
    {
        return JenisPelanggaran::where('kategori_id', $kategoriId)
            ->select('id', 'kategori_id', 'jenis_pelanggaran', 'poin', 'sub_kategori')
            ->get();
    }

    private function getKategoriPelanggaran()
    {
        return KategoriPelanggaran::select('id', 'name')->get();
    }

    public function editPelanggaranIdKategori($id)
    {
        $title = 'Edit Data Pelanggaran';
        $kategoriPelanggaran = $this->getKategoriPelanggaran();
        $i = 65; // ASCII value for 'A'
        foreach ($kategoriPelanggaran as $kategori) {
            $kategori->AZ = chr($i);
            $i++;
        }
        $id_kategori = $id;
        $name_form = KategoriPelanggaran::where('id', $id)->select('name')->first();
        $jenisPelanggaran = $this->getJenisPelanggaran($id);
        return view('admin.edit-pelanggaran', compact('title', 'kategoriPelanggaran', 'jenisPelanggaran', 'id_kategori', 'name_form'));
    }

    public function editPelanggaranStore(Request $request, $id_kategori)
    {
        try {
            $groupedData = [];
            foreach ($request->all() as $key => $value) {
                if (preg_match('/^jenis_pelanggaran_(\d+)$/', $key, $matches)) {
                    $id = $matches[1];
                    $groupedData[$id]['jenis_pelanggaran'] = $value;
                    $groupedData[$id]['poin'] = $request->input("poin_$id");
                    $groupedData[$id]['sub_kategori'] = $request->input("sub_kategori_$id");
                }
            }
            $searchJenisPelanggaran = JenisPelanggaran::where('kategori_id', $id_kategori)->get();
            $changedJenisIds = [];

            foreach ($searchJenisPelanggaran as $jenisPelanggaran) {
                $id = $jenisPelanggaran->id;
                $jenisData = $groupedData[$id] ?? null;
                if ($jenisData && (
                    $jenisPelanggaran->jenis_pelanggaran != $jenisData['jenis_pelanggaran'] ||
                    $jenisPelanggaran->poin != $jenisData['poin'] ||
                    $jenisPelanggaran->sub_kategori != $jenisData['sub_kategori']
                )) {
                    $changedJenisIds[] = $id;
                }
            }

            if (count($changedJenisIds) === 0) {
                return redirect()->route('admin.editPelanggaranIdKategori', $id_kategori)->with('error', 'Tidak ada perubahan pada data pelanggaran');
            }

            // Update data pelanggaran
            foreach ($changedJenisIds as $id) {
                $jenisPelanggaran = JenisPelanggaran::find($id);
                $jenisPelanggaran->jenis_pelanggaran = $groupedData[$id]['jenis_pelanggaran'];
                $jenisPelanggaran->poin = $groupedData[$id]['poin'];
                $jenisPelanggaran->sub_kategori = $groupedData[$id]['sub_kategori'];
                $jenisPelanggaran->save();
            }
            return redirect()->route('admin.editPelanggaranIdKategori', $id_kategori)->with('success', 'Data pelanggaran berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', $id_kategori)->with('error', 'Data pelanggaran gagal diperbarui');
        }
    }

    public function deleteJenisPelanggaran($id)
    {
        try {
            $jenisPelanggaran = JenisPelanggaran::where('id', $id)->first();
            $jenisPelanggaran->delete();
            return redirect()->route('admin.editPelanggaranIdKategori', $jenisPelanggaran->kategori_id)->with('success', 'Berhasil menghapus data pelanggaran');
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', $jenisPelanggaran->kategori_id)->with('error', 'Terjadi kesalahan saat menghapus data pelanggaran: ' . $e->getMessage());
        }
    }

    public function deleteKategori($id)
    {
        try {
            $kategoriPelanggaran = KategoriPelanggaran::where('id', $id)->first();
            $kategoriPelanggaran->delete();
            return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('success', 'Berhasil menghapus data kategori pelanggaran');
        } catch (\Exception $e) {
            return redirect()->route('admin.editPelanggaranIdKategori', 1)->with('error', 'Terjadi kesalahan saat menghapus data kategori pelanggaran: ' . $e->getMessage());
        }
    }

    public function laporanPelanggaran()
    {
        $title = 'Laporan Pelanggaran';
        $today = now()->toDateString();

        $submitted = Pelanggaran::with('user')
            ->whereIn('statusPelanggaran', ['submitted'])
            ->orderByRaw("ABS(DATEDIFF(date, '$today'))") // Urutkan berdasarkan perbedaan tanggal
            ->paginate(10);

        $rejected = Pelanggaran::with('user')
            ->whereIn('statusPelanggaran', ['rejected'])
            ->orderByRaw("ABS(DATEDIFF(date, '$today'))") // Urutkan berdasarkan perbedaan tanggal
            ->paginate(10);

        $progressing = Pelanggaran::with('user')
            ->whereIn('statusPelanggaran', ['progressing'])
            ->orderByRaw("ABS(DATEDIFF(date, '$today'))") // Urutkan berdasarkan perbedaan tanggal
            ->paginate(10);

        $totalSubmitted = Pelanggaran::whereIn('statusPelanggaran', ['submitted'])->count();
        $totalRejected = Pelanggaran::whereIn('statusPelanggaran', ['rejected'])->count();
        $totalProgressing = Pelanggaran::whereIn('statusPelanggaran', ['progressing'])->count();

        $dataTotal = [
            'totalSubmitted' => $totalSubmitted,
            'totalRejected' => $totalRejected,
            'totalProgressing' => $totalProgressing,
        ];

        arsort($dataTotal);

        $keyPagination = key($dataTotal);  // Mengambil kunci dari elemen pertama

        if ($keyPagination === 'totalSubmitted') {
            $keyPagination = 'submitted';
        } elseif ($keyPagination === 'totalRejected') {
            $keyPagination = 'rejected';
        } elseif ($keyPagination === 'totalProgressing') {
            $keyPagination = 'progressing';
        }

        // dd($keyPagination);

        $allData = [
            'submitted' => $submitted,
            'rejected' => $rejected,
            'progressing' => $progressing,
        ];
        // dd($allData);

        return view('admin.laporan-pelanggaran', compact('title', 'allData', 'keyPagination'));
    }

    public function laporanPelanggaranOpen($id)
    {
        $data = Pelanggaran::where('id', $id)->first();
        $blok = blokRuangan::where('id', $data->user->blok_ruangan_id)
            ->select('name')
            ->first();
        if ($blok == null) {
            return redirect()->route('admin.laporanPelanggaran')->with('error', 'Mahasiswa Belum Melengkapi data.');
        }
        $jenisPelanggaran = JenisPelanggaran::where('id', $data->jenis_pelanggaran_id)
            ->select('jenis_pelanggaran', 'kategori_id', 'poin', 'sub_kategori')
            ->first();
        $kategoriPelanggaran = KategoriPelanggaran::where('id', $jenisPelanggaran->kategori_id)
            ->select('name')
            ->first();
        $AZ = chr(65 + $jenisPelanggaran->kategori_id - 1);
        $data->blok = $blok->name;
        $data->kategori_pelanggaran = $kategoriPelanggaran->name;
        $data->AZ = $AZ;
        $data->jenis_pelanggaran = $jenisPelanggaran->jenis_pelanggaran;
        $data->poin = $jenisPelanggaran->poin;
        $data->sub_kategori = $jenisPelanggaran->sub_kategori;
        $title = 'Laporan Pelanggaran';
        // dd($data);
        return view('admin.admin-edit.show-laporan', compact('title', 'data'));
    }

    public function laporanPelanggaranRejected(Request $request, $id)
    {
        try {
            if ($request->rejected_message == null) {
                return redirect()->route('admin.laporanPelanggaranOpen', $id)->with('error', 'Alasan penolakan tidak boleh kosong.');
            }
            $jenisPelanggaran = Pelanggaran::where('id', $id)->first();
            $jenisPelanggaran->statusPelanggaran = 'rejected';
            $jenisPelanggaran->rejected_message = $request->rejected_message;
            $jenisPelanggaran->save();
            return redirect()->route('admin.laporanPelanggaran')->with('success', 'Status pelanggaran berhasil diperbarui menjadi Rejected');
        } catch (\Exception $e) {
            return redirect()->route('admin.laporanPelanggaranOpen', $id)->with('error', 'Terjadi kesalahan saat pembaruan status pelanggaran: ' . $e->getMessage());
        }
    }

    public function laporanPelanggaranDeleted($id)
    {
        try {
            $jenisPelanggaran = Pelanggaran::where('id', $id)->first();
            $jenisPelanggaran->delete();
            return redirect()->route('admin.laporanPelanggaran')->with('success', 'Berhasil menghapus data pelanggaran');
        } catch (\Exception $e) {
            return redirect()->route('admin.laporanPelanggaran')->with('error', 'Terjadi kesalahan saat pembaruan status pelanggaran: ' . $e->getMessage());
        }
    }

    public function laporanPelanggaranConfirm(Request $request, $id)
    {
        if ($id == null) {
            return redirect()->route('admin.laporanPelanggaran')->with('error', 'ID pelanggaran tidak ditemukan');
        }
        try {
            $request->validate([
                'hukuman' => 'required'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.laporanPelanggaranOpen', $id)->with('error', 'Hukuman tidak boleh kosong.');
        }

        try {
            $searchPelanggaran = Pelanggaran::where('id', $id)->first();
            $searchPelanggaran->hukuman = $request->hukuman;
            $searchPelanggaran->statusPelanggaran = 'progressing';
            $searchPelanggaran->accepted_id = Auth::user()->id;
            $searchPelanggaran->save();
            return redirect()->route('admin.laporanPelanggaran')->with('success', 'Berhasil mengkonfirmasi pelanggaran');
        } catch (\Exception $e) {
            return redirect()->route('admin.laporanPelanggaranOpen', $id)->with('error', 'Terjadi kesalahan saat mengkonfirmasi pelanggaran: ' . $e->getMessage());
        }
    }

    public function laporanPelanggaranDone($id)
    {
        try {
            $jenisPelanggaran = Pelanggaran::where('id', $id)->first();
            $jenisPelanggaran->statusPelanggaran = 'Done';
            $jenisPelanggaran->save();
            return redirect()->route('admin.laporanPelanggaran')->with('success', 'Berhasil mengkonfirmasi pelanggaran dengan status selesai');
        } catch (\Exception $e) {
            return redirect()->route('admin.laporanPelanggaran')->with('error', 'Terjadi kesalahan saat pembaruan status pelanggaran: ' . $e->getMessage());
        }
    }
}
