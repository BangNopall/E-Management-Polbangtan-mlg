<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Models\JadwalKegiatanAsrama;
use App\Models\JadwalPetugas;
use App\Models\Kelas;
use App\Models\LoginPermission;
use App\Models\Pelanggaran;
use App\Models\Presence;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\PresensiUpacara;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class DashboardAdminController extends Controller
{
    public function showDataPetugas(Request $request)
    {
        $role_id = auth()->user()->role_id;
        $search = $request->input('search');
        $filter_role = $request->input('role_id');

        $query = User::where('role_id', '!=', 3)
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filter_role, function ($q) use ($filter_role) {
                return $q->where('role_id', $filter_role);
            });

        if ($role_id == 2) {
            $petugas = $query->orderBy('role_id', 'asc')
                ->select('id', 'name', 'email', 'role_id', 'image')
                ->paginate(20)->withQueryString();
        } else {
            $petugas = $query->with([
                'roleId' => function ($q) {
                    $q->select('id', 'name');
                },
            ])
                ->orderBy('role_id', 'asc')
                ->select('id', 'name', 'email', 'role_id', 'image')
                ->paginate(20)->withQueryString();
        }

        $roles = Role::where('id', '!=', 3)->get();
        $title = 'Data Petugas';

        return view('admin.data-petugas', compact('petugas', 'title', 'roles', 'search', 'filter_role'));
    }

    public function createDataPetugasShow()
    {
        $title = 'Tambah Petugas';
        $roles = Role::where('id', '!=', 3)->get();

        return view('admin.admin-edit.create-data-petugas', compact('title', 'roles'));
    }

    public function createDataPetugas(Request $request)
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
        if (! $user) {
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

        $title = 'Edit Data Petugas';

        // dd($user);
        return view('admin.admin-edit.edit-data-petugas', compact('user', 'roles', 'title'));
    }

    public function editDataPetugas(Request $request, $id)
    {
        // Validasi data yang diterima dari formulir
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email,'.$id,
            'name' => 'required|string|max:255',
            'role_id' => 'required|integer',
            'reset_password' => 'required|string|min:5',
            'new_password' => 'nullable|string|min:5',
            'new_password_confirmation' => 'nullable|string|min:5|same:new_password',
        ]);

        // Ambil pengguna berdasarkan ID dari formulir
        $user = User::findOrFail($id);

        if ($user->id != Auth()->user()->id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit pengguna lain.');
        }

        if (! Hash::check($request->old_password, $user->password)) {
            return redirect()->back()->with('error', 'Password lama salah.');
        }

        if ($request->old_password == $request->new_password) {
            return redirect()->back()->with('error', 'Password baru tidak boleh sama dengan password lama.');
        }

        // Periksa apakah password reset yang dimasukkan benar
        if (! password_verify($validatedData['reset_password'], $user->password)) {
            return redirect()->back()->with('error', 'Password lama salah.');
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

    public function destroyDataPetugas(Request $request, $id)
    {
        // Temukan pengguna berdasarkan ID
        $user = User::find($id);

        // Periksa apakah pengguna ditemukan
        if (! $user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        if ($user->id == auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
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
        $title = 'Piket Petugas';
        $query = JadwalPetugas::with(['petugas1', 'petugas2'])->latest('date');

        // Proses pencarian jika parameter 'search' ditemukan
        if ($request->has('search')) {
            $searchDate = $request->input('search');
            $query->where('date', $searchDate);
        }

        $petugas = $query->paginate(20)->withQueryString();
        $users = User::where('role_id', 2)->get();
        $existingDates = JadwalPetugas::pluck('date')->toArray();

        return view('admin.piket-petugas', compact('title', 'petugas', 'users', 'existingDates'));
    }

    public function showPiketPetugasSingle(Request $request, $id)
    {
        $title = 'Edit Piket Petugas';
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
            return redirect()->back()->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function piketPetugasGenerateJadwalBulanan()
    {
        // Ambil data petugas dengan role_id = 2
        $users = User::where('role_id', 2)->get();

        // Ambil tanggal hari terakhir dari jadwal yang sudah ada
        $lastSchedule = JadwalPetugas::latest('date')->first();

        // Tentukan tanggal awal untuk jadwal baru
        $startDate = $lastSchedule ? Carbon::parse($lastSchedule->date)->addDay() : Carbon::today();

        // Jika tidak ada jadwal yang ada, mulai dari hari ini
        if (! $lastSchedule) {
            $startDate = Carbon::today();
        }

        // Buat jadwal untuk 30 hari ke depan
        for ($i = 0; $i < 30; $i++) {
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

    public function piketPetugasGenerateJadwalMingguan()
    {
        // Ambil data petugas dengan role_id = 2
        $users = User::where('role_id', 2)->get();

        // Ambil tanggal hari terakhir dari jadwal yang sudah ada
        $lastSchedule = JadwalPetugas::latest('date')->first();

        // Tentukan tanggal awal untuk jadwal baru
        $startDate = $lastSchedule ? Carbon::parse($lastSchedule->date)->addDay() : Carbon::today();

        // Jika tidak ada jadwal yang ada, mulai dari hari ini
        if (! $lastSchedule) {
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
        $title = 'Scan QR Absen Keluar';
        $user = auth()->user();

        $jadwalPiket = JadwalPetugas::where('date', now()->format('Y-m-d'))->first();

        if ($jadwalPiket == null) {
            return redirect()->route('admin.index')->with('error', 'Jadwal piket untuk hari ini tidak ditemukan silahkan hubungi admin/operator untuk membuat jadwal piket terlebih dahulu.');
        }
        $petugas1 = User::where('id', $jadwalPiket->petugas1_id)->value('name');
        $petugas2 = User::where('id', $jadwalPiket->petugas2_id)->value('name');

        return view('admin.kamera', compact('title', 'petugas1', 'petugas2', 'user'));
    }

    public function showSistemAdmin()
    {
        $title = 'Sistem Admin';

        return view('admin.sistem', compact('title'));
    }

    public function downloadExcelTemplate()
    {
        $fileTemplate = public_path('excel/datauser-template.xlsx');

        return response()->download($fileTemplate, 'datauser-template.xlsx', [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    // { START OF UPGRADE CLASS SISTEM ADMIN }
    public function upgradeClassSistemAdmin(Request $request)
    {
        $users = User::where('role_id', 3)->get();
        foreach ($users as $user) {
            $currentClassId = $user->kelas_id;
            $kelas = $this->findClassById($currentClassId);
            // dd($kelas);

            if (! $kelas) {
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

        $jadwalKegiatanAsrama = JadwalKegiatanAsrama::where('blok_id', $user->blok_ruangan_id)->get();

        // Pengecekan apakah hasil query tidak kosong
        if (! $jadwalKegiatanAsrama->isEmpty()) {
            // Menghapus semua data jadwal kegiatan
            JadwalKegiatanAsrama::where('blok_id', $user->blok_ruangan_id)->delete();
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
            $import = new UsersImport;
            Excel::import($import, $request->file('file'));

            return redirect()->route('admin.sistem-admin')->with('success', 'Data users imported successfully.');
        } catch (\Exception $e) {
            return redirect()->route('admin.sistem-admin')->with('error', 'Error importing data. '.$e->getMessage());
        }
    }

    // { END OF IMPORT CLASS SISTEM ADMIN }

}
