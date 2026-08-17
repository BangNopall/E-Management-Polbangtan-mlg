<?php

namespace App\Http\Controllers;

use App\Models\blokRuangan;
use App\Models\Kelas;
use App\Models\prodi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4 || $user->role_id == 5) {
            return redirect()->route('admin.profil', $user->id);
        }

        if ($user->role_id == 3) {
            return redirect()->route('home.profilshow', $user->id);
        }
    }

    public function profil()
    {
        $user = auth()->user();
        $blocks = blokRuangan::all();
        $prodis = prodi::all();
        $kelas = Kelas::all();

        $title = 'Profil';

        return view('profil', compact('user', 'blocks', 'prodis', 'kelas', 'title'));
    }

    public function editProfile(Request $request, $id)
    {
        // Temukan pengguna berdasarkan ID
        $user = User::find($id);

        // Periksa apakah pengguna ditemukan
        if (! $user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'blok_ruangan_id' => 'required|exists:blok_ruangans,id',
            'no_kamar' => 'required|numeric',
            'asal_daerah' => 'required|string|max:255',
            'foto-profil' => 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:10250',
        ];

        // Only require NIM and Prodi if user is NOT a student
        if (!$user->isUser()) {
            $rules['nim'] = 'required|numeric|min:11|unique:users,nim,'.$id;
            $rules['prodi_id'] = 'required';
        }

        // Validasi data dari formulir
        $request->validate($rules);

        if (!$user->isUser()) {
            $existingUser = User::where('nim', $request->nim)->first();
            if ($existingUser && $existingUser->id != $user->id) {
                return redirect()->back()->with('error', 'NIM sudah digunakan oleh pengguna lain.');
            }
        }

        // Cek apakah pengguna telah mengunggah file foto baru
        if ($request->hasFile('foto-profil')) {
            $this->updateProfilePicture($request, $user);
        }

        // Update data pengguna dengan data yang baru
        if ($user->isAdmin() || $user->isOperator() || $user->isPelatih() || $user->isPembina()) {
            $user->nim = $request->nim;
            $user->name = $request->name;
            $user->prodi_id = $request->prodi_id;
            $user->kelas_id = $request->kelas_id;
            $user->blok_ruangan_id = $request->blok_ruangan_id;
            $user->no_kamar = $request->no_kamar;
            $user->asal_daerah = $request->asal_daerah;
            $user->save();

            return redirect()->route('admin.profil', $user->id)->with('success', 'Profil berhasil diperbarui.');
        } elseif ($user->isUser()) {
            // script baru
            $cariKelas = Kelas::where('id', $request->kelas_id)->first();
            if ($cariKelas->prodi_id == $user->prodi_id) { // Compare with existing prodi_id
                // Do not update NIM and prodi_id
                $user->name = $request->name;
                $user->kelas_id = $request->kelas_id;
                $user->blok_ruangan_id = $request->blok_ruangan_id;
                $user->no_kamar = $request->no_kamar;
                $user->asal_daerah = $request->asal_daerah;
                $user->save();

                return redirect()->route('home.profilshow', $user->id)->with('success', 'Profil berhasil diperbarui.');
            } else {
                return redirect()->back()->with('error', 'Prodi dan Kelas tidak sesuai.');
            }
        } else {
            return redirect()->back()->with('error', 'AKSES DITOLAK!');
        }
    }

    private function updateProfilePicture($request, $user)
    {
        // Hapus foto lama
        if ($user->image) {
            Storage::delete('/public/images/'.$user->image);
        }

        // Upload file foto baru ke storage
        try {
            $file = $request->file('foto-profil');

            // Generasi nama file yang unik dan aman
            // Menggunakan GUID untuk menjamin keunikan
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Simpan file ke storage/app/public/images
            $path = $file->storeAs('images', $filename, 'public');

            if (! $path) {
                throw new \RuntimeException('Gagal menyimpan file foto.');
            }

            // Perbarui URL foto di database
            $user->image = $filename;
        } catch (\Throwable $th) {
            // Tangkap semua kemungkinan error saat upload
            throw new \RuntimeException('Gagal memperbarui foto profil: ' . $th->getMessage(), 0, $th);
        }

        // $file = $request->file('foto-profil');
        // $filename = Str::random(10).'-'.$file->getClientOriginalName();
        // $file->storeAs('images', $filename, 'public');

        // // Perbarui URL foto di database
        // $user->image = $filename;
    }

    public function editProfileGmail(Request $request, $id)
    {
        try {
            $request->validate([
                'no_hp' => 'required|numeric|digits_between:10,13|unique:users,no_hp,'.$id,
                'email' => [
                    'required',
                    'email:dns',
                    'unique:users,email,'.$id,
                ],
                'password' => 'min:5|nullable',
            ]);

            $user = User::find($id);
            // dd($user);
            $existingUser = User::where('no_hp', $request->no_hp)->first();
            if ($existingUser && $existingUser->id != $user->id) {
                return redirect()->back()->with('error', 'Nomor HP sudah digunakan oleh pengguna lain.');
            }

            if (! $user) {
                return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
            }

            $user->no_hp = $request->no_hp;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            if ($user->isAdmin() || $user->isOperator() || $user->isPelatih() || $user->isPembina()) {
                return redirect()->route('admin.profil', $user->id)->with('success-email', 'Informasi akun berhasil diperbarui.');
            } else {
                return redirect()->route('home.profilshow', $user->id)->with('success-email', 'Informasi akun berhasil diperbarui.');
            }
        } catch (\Exception $e) {
            $user = User::find($id);
            if ($user->isAdmin() || $user->isOperator() || $user->isPelatih() || $user->isPembina()) {
                return redirect()->route('admin.profil', $user->id)->with('error-email', 'Informasi akun Gagal diperbarui.');
            } else {
                return redirect()->route('home.profilshow', $user->id)->with('error-email', 'Informasi akun Gagal diperbarui.');
            }
        }
    }

    public function deleteFotoProfile($id)
    {
        // dd($id);
        $user = User::find($id);
        if ($user->image) {
            Storage::delete('/public/images/'.$user->image);
            $user->image = null;
            $user->save();

            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Foto profil tidak ditemukan.');
        }
    }
}
