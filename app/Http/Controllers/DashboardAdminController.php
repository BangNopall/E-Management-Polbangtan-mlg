<?php

namespace App\Http\Controllers;

use App\Models\jadwalKegiatanAsrama;
use App\Models\LoginPermission;
use App\Models\Pelanggaran;
use App\Models\Presence;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\PresensiUpacara;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Kelas;
use App\Models\BlokRuangan;
use Illuminate\Http\Request;
use App\Models\JadwalPetugas;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use Illuminate\Support\Facades\DB;

class DashboardAdminController extends Controller
{
    public function showDataPetugas()
    {
        $role_id = auth()->user()->role_id;

        if ($role_id == 2) {
            $petugas = User::whereIn('role_id', [1, 2, 4])
                ->orderBy('role_id', 'asc')
                ->select('id', 'name', 'email', 'role_id', 'image')
                ->get();
        } else {
            $petugas = User::whereIn('role_id', [1, 2, 4])
                ->with([
                    'roleId' => function ($query) {
                        $query->select('id', 'name');
                    }
                ])
                ->orderBy('role_id', 'asc')
                ->select('id', 'name', 'email', 'role_id', 'image')
                ->get();
        }

        $title = "Data Petugas";

        // dd($petugas);

        return view('admin.data-petugas', compact('petugas', 'title'));
    }

    public function createDataPetugasShow()
    {
        $title = "Tambah Petugas";

        return view('admin.admin-edit.create-data-petugas', compact('title'));
    }

    public function createDataPetugas(request $request)
    {
        // Validasi data yang diterima dari formulir
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|max:255',
            'password' => 'required|min:5|confirmed',
            'role_id' => 'required|integer',
        ]);

        // Buat user baru
        User::create([
            'email' => $request->email,
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'blok_ruangan_id' => 1,
            // Enkripsi password
        ]);

        // Redirect ke halaman lain atau tampilkan pesan sukses
        return redirect()->route('admin.dataPetugas')->with('success', 'Petugas berhasil didaftarkan.');
    }

    public function editDataPetugasShow($id)
    {

        if (Auth()->user()->role_id == 4) {
            $roles = Role::where('id', 4)->get();
        }
        if (Auth()->user()->role_id == 2) {
            $roles = Role::where('id', 2)->get();
        }
        if (Auth()->user()->role_id == 1) {
            $roles = Role::where('id', '!=', 3)->get();
        }

        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin.dataPetugas')->with('error', 'Pengguna tidak ditemukan.');
        }

        if (Auth()->user()->role_id == 2) {
            if ($user->id != Auth()->user()->id) {
                return redirect()->route('admin.dataPetugas')->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna lain.');
            }
        }

        if (Auth()->user()->role_id == 4) {
            if ($user->id != Auth()->user()->id) {
                return redirect()->route('admin.dataPetugas')->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna lain.');
            }
        }

        $title = "Edit Data Petugas";

        // dd($user);
        return view('admin.admin-edit.edit-data-petugas', compact('user', 'roles', 'title'));
    }
    public function editDataPetugas(Request $request, $id)
    {
        // Validasi data yang diterima dari formulir
        $validatedData = $request->validate([
            'email' => 'email',
            'name' => 'string|max:255',
            'role_id' => 'integer',
            'reset_password' => 'string|min:5',
            'new_password' => 'string|min:5|nullable',
            'new_password_confirmation' => 'string|min:5|nullable',
        ]);

        // Ambil pengguna berdasarkan ID dari formulir
        $user = User::findOrFail($id);


        // Periksa apakah pengguna ditemukan
        if (!$user) {
            return redirect()->route('admin.editDataPetugasShow')->with('error', 'Pengguna tidak ditemukan.');
        }

        if (Auth()->user()->role_id == 2) {
            if ($user->id != Auth()->user()->id) {
                return redirect()->route('admin.editDataPetugasShow')->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna lain.');
            }
        }

        // dd($validatedData);
        // Periksa apakah password reset yang dimasukkan benar
        if (!password_verify($validatedData['reset_password'], $user->password)) {
            return redirect()->route('admin.editDataPetugasShow')->with('error', 'Password reset salah.');
        }
        // dd('password-lolos');


        // Periksa apakah new_password dan password_confirmation nilainya sama
        if ($validatedData['new_password'] !== $validatedData['new_password_confirmation']) {
            return redirect()->route('admin.editDataPetugasShow')->with('error', 'Password baru dan konfirmasi password tidak cocok.');
        }


        // Jika ada password baru, hash password baru dan update pengguna
        if ($request->filled('new_password')) {
            $user->password = bcrypt($validatedData['new_password']);
        }

        // Update pengguna dengan data baru
        $user->email = $validatedData['email'];
        $user->name = $validatedData['name'];
        $user->role_id = $validatedData['role_id'];
        $user->save();
        // dd('lolos');

        return redirect()->route('admin.dataPetugas')->with('success', 'Informasi petugas berhasil diperbarui.');
    }



    public function destroyDataPetugas(request $request, $id)
    {
        // Temukan pengguna berdasarkan ID
        $user = User::find($id);

        // Periksa apakah pengguna ditemukan
        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        if (Auth()->user()->role_id == 2) {
            if ($user->id != Auth()->user()->id) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna lain.');
            }
        }

        // Perbarui data di tabel terkait (jadwal_petugas)
        DB::table('jadwal_petugas')
            ->where('petugas1_id', $user->id)
            ->update(['petugas1_id' => null]);

        DB::table('jadwal_petugas')
            ->where('petugas2_id', $user->id)
            ->update(['petugas2_id' => null]);

        $user->delete();

        return redirect()->route('admin.dataPetugas')->with('success', 'Pengguna berhasil dihapus.');
    }


    public function showPiketPetugas(Request $request)
    {
        $title = "Piket Petugas";
        $query = JadwalPetugas::with(['petugas1', 'petugas2'])->latest('date');

        // Proses pencarian jika parameter 'search' ditemukan
        if ($request->has('search')) {
            $searchDate = $request->input('search');
            $query->where('date', $searchDate);
        }

        $petugas = $query->paginate(10);
        $users = User::where('role_id', 2)->get();
        $existingDates = JadwalPetugas::pluck('date')->toArray();

        return view('admin.piket-petugas', compact('title', 'petugas', 'users', 'existingDates'));
    }

    public function showPiketPetugasSingle(Request $request, $id)
    {
        $title = "Edit Piket Petugas";
        $jadwal = JadwalPetugas::findOrFail($id);
        $users = User::where('role_id', 2)->get();

        return view('admin.piket-petugas-single', compact('title', 'jadwal', 'users'));
    }

    public function updatePiketPetugas(Request $request, $id)
    {
        // Validasi form jika diperlukan
        $request->validate([
            // Atur aturan validasi sesuai kebutuhan
        ]);

        // Ambil data jadwal piket berdasarkan ID
        $jadwal = JadwalPetugas::findOrFail($id);

        // Dapatkan nilai petugas1_id dari formulir
        $petugas1Id = $request->input('petugas1');

        // Dapatkan nilai petugas2_id dari formulir
        $petugas2Id = $request->input('petugas2');

        // Cek jika $petugas1Id tidak null, gunakan nilai baru, jika null, gunakan nilai lama
        $jadwal->update([
            'petugas1_id' => $petugas1Id ?? $jadwal->petugas1_id,
            'petugas2_id' => $petugas2Id ?? $jadwal->petugas2_id,
        ]);

        // Redirect atau kembalikan response sesuai kebutuhan
        return redirect()->route('admin.piketPetugas')->with('success', 'Jadwal piket berhasil diperbarui.');
    }


    public function deletePiketPetugasSingle($id)
    {
        // Temukan dan hapus data piket petugas berdasarkan ID
        JadwalPetugas::findOrFail($id)->delete();

        // Redirect atau kembalikan response sesuai kebutuhan
        return redirect()->route('admin.piketPetugas')->with('success', 'Jadwal piket berhasil dihapus.');
    }

    public function piketPetugasGenerateJadwal(Request $request)
    {
        try {
            $request->validate([
                'jadwalDate' => 'required|date|unique:jadwal_petugas,date',
                'petugas1' => 'required|exists:users,id',
                'petugas2' => 'required|exists:users,id',
                // tambahkan aturan validasi lainnya sesuai kebutuhan
            ]);

            // Proses membuat jadwal baru
            $jadwal = new JadwalPetugas;
            $jadwal->date = $request->jadwalDate;
            $jadwal->petugas1_id = $request->petugas1;
            $jadwal->petugas2_id = $request->petugas2;
            // tambahkan atribut dan logika lainnya sesuai kebutuhan
            $jadwal->save();

            return redirect()->route('admin.piketPetugas')->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }



    public function piketPetugasGenerateJadwalMingguan()
    {
        // Ambil data petugas dengan role_id = 2
        $users = User::where('role_id', 2)->get();

        // Ambil tanggal hari terakhir dari jadwal yang sudah ada
        $lastSchedule = JadwalPetugas::latest('date')->first();

        // Tentukan tanggal awal untuk jadwal baru
        $startDate = $lastSchedule ? Carbon::parse($lastSchedule->date)->addDay() : Carbon::today();

        // Jika tidak ada jadwal yang ada, mulai dari hari ini
        if (!$lastSchedule) {
            $startDate = Carbon::today();
        }

        // Buat jadwal untuk 7 hari ke depan
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);

            // Simpan jadwal dengan petugas1_id dan petugas2_id kosong
            JadwalPetugas::create([
                'date' => $date,
                'petugas1_id' => null,
                'petugas2_id' => null,
            ]);
        }

        // Redirect atau kembalikan response sesuai kebutuhan
        return redirect()->route('admin.piketPetugas')->with('success', 'Jadwal piket berhasil dibuat.');
    }

    public function showKamera()
    {
        $title = "Scan QR Absen Keluar";
        $user = auth()->user();

        $jadwalPiket = JadwalPetugas::where('date', now()->format('y-m-d'))->first();

        if ($jadwalPiket == null) {
            return redirect()->route('admin.piketPetugas')->with('error', 'Jadwal piket untuk hari ini tidak ditemukan silahkan buat jadwal terlebih dahulu.');
        }
        $petugas1 = User::where('id', $jadwalPiket->petugas1_id)->value('name');
        $petugas2 = User::where('id', $jadwalPiket->petugas2_id)->value('name');
        return view('admin.kamera', compact('title', 'petugas1', 'petugas2', 'user'));
    }

    public function showSistemAdmin()
    {
        $title = "Sistem Admin";

        return view('admin.sistem', compact('title'));
    }

    public function downloadExcelTemplate()
    {
        $fileTemplate = public_path('excel/datauser-template.xlsx');
        return response()->download($fileTemplate);
    }

    // { START OF UPGRADE CLASS SISTEM ADMIN }
    public function upgradeClassSistemAdmin(Request $request)
    {
        $users = User::where('role_id', 3)->get();
        foreach ($users as $user) {
            $currentClassId = $user->kelas_id;
            $kelas = $this->findClassById($currentClassId);
            // dd($kelas);

            if (!$kelas) {
                $this->handleClassNotFound($user);
                continue;
            }

            $kelasBaru = $kelas->kelas + 1;
            $data = $this->findNewClass($kelas, $kelasBaru);
            // dd($data);

            // dd($data);

            if ($data->isEmpty()) {
                $this->handleNewClassNotFound($user);
                $this->deleteUser($user);
                continue;
            }
            $this->updateUserClass($user);
        }

        return redirect()->back()->with('success', 'Upgrade kelas berhasil dilakukan.');
        // return redirect()->back()->with('success', $data);
    }

    private function findClassById($classId)
    {
        return Kelas::find($classId);
    }

    private function handleClassNotFound($user)
    {
        Log::error("Kelas tidak ditemukan untuk user ID {$user->id}");
    }

    private function findNewClass($kelas, $kelasBaru)
    {
        return Kelas::where('kelas', $kelasBaru)
            ->where('prodi_id', $kelas->prodi_id)
            ->where('level_kelas_id', $kelas->level_kelas_id)
            ->get(['id']);
    }

    private function handleNewClassNotFound($user)
    {
        Log::error("Data kelas baru tidak ditemukan untuk user ID {$user->id} Atas Nama - {$user->name} #USER DITETAPKAN DISMANTLE");
    }

    private function deleteUser($user)
    {
        $login = LoginPermission::where('user_id', $user->id)->first();
        if ($login) {
            $login->delete();
        }

        // Delete related records in the presences table
        Presence::where('user_id', $user->id)->delete();

        $user->delete();
    }


    private function updateUserClass($user)
    {
        $user->update(['kelas_id' => null]);
        $user->update(['blok_ruangan_id' => null]);
        $user->update(['no_kamar' => null]);


        // lanjut ngoding buat hapus data pelanggaran dan presensi absen keluar masuk dan absen kegiatan wajib
        $presensi = Presence::where('user_id', $user->id)->get();
        foreach ($presensi as $presensi) {
            $presensi->delete();
        }

        $pelanggaran = Pelanggaran::where('user_id', $user->id)->get();
        foreach ($pelanggaran as $pelanggaran) {
            $pelanggaran->delete();
        }

        $presensiUpacara = PresensiUpacara::where('user_id', $user->id)->get();
        foreach ($presensiUpacara as $presensiUpacara) {
            $presensiUpacara->delete();
        }

        $presensiApel = PresensiApel::where('user_id', $user->id)->get();
        foreach ($presensiApel as $presensiApel) {
            $presensiApel->delete();
        }

        $presensiSenam = PresensiSenam::where('user_id', $user->id)->get();
        foreach ($presensiSenam as $presensiSenam) {
            $presensiSenam->delete();
        }

        $jadwalKegiatanAsrama = jadwalKegiatanAsrama::where('blok_id', $user->blok_ruangan_id)->get();

        // Pengecekan apakah hasil query tidak kosong
        if (!$jadwalKegiatanAsrama->isEmpty()) {
            // Menghapus semua data jadwal kegiatan
            jadwalKegiatanAsrama::where('blok_id', $user->blok_ruangan_id)->delete();
        }
        // dd($pelanggaran);

    }

    // { END OF UPGRADE CLASS SISTEM ADMIN }

    // { START OF IMPORT CLASS SISTEM ADMIN }
    public function importClassSistemAdmin(Request $request)
    {
        // dd ($request->all());

        $request->validate([
            'file' => 'required|mimes:xls,xlsx',
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));

            return redirect()->route('admin.sistem-admin')->with('success', 'Data users imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sistem-admin')->with('error', 'Error importing data. ' . $e->getMessage());
        }
    }


    // { END OF IMPORT CLASS SISTEM ADMIN }

}
