<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\prodi;
use App\Models\Kelas;
use App\Models\blokRuangan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4) {
            return redirect()->route('admin.profil', $user->id);
        }

        if ($user->role_id == 3) {
            return redirect()->route('home.profilshow', $user->id);
        }
    }
    public function profil()
    {
        $user = auth()->user();
        $blocks = BlokRuangan::all();
        $prodis = prodi::all();
        $kelas = kelas::all();

        $title = "Profil";

        return view('profil', compact('user', 'blocks', 'prodis', 'kelas', 'title'));
    }

    public function editProfile(Request $request, $id)
    {
        // dd($request->all());
        // Validasi data dari formulir
        $request->validate([
            'nim' => 'required|numeric|min:11|unique:users,nim,' . $id , 
            'name' => 'required|string|max:255',
            'prodi_id' => 'required',
            'kelas_id' => 'required|exists:kelas,id',
            'blok_ruangan_id' => 'required|exists:blok_ruangans,id',
            'no_kamar' => 'required|numeric',
            'asal_daerah' => 'required|string|max:255',
            'foto-profil' => 'nullable|image|mimes:jpeg,png,jpg',
            // Tambahkan aturan validasi lainnya sesuai kebutuhan
        ]);

        // Temukan pengguna berdasarkan ID
        $user = User::find($id);


        $existingUser = User::where('nim', $request->nim)->first();
        if ($existingUser && $existingUser->id != $user->id) {
            return redirect()->back()->with('error', 'NIM sudah digunakan oleh pengguna lain.');
        }
        // Cek apakah pengguna telah mengunggah file foto baru
        if ($request->hasFile('foto-profil')) {
            $this->updateProfilePicture($request, $user);
        }

        // Periksa apakah pengguna ditemukan
        if (!$user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        // Update data pengguna dengan data yang baru
        if ($user->isAdmin() || $user->isOperator() || $user->isPelatih()) {
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
            $cariKelas = kelas::where('id', $request->kelas_id)->first();
            if ($cariKelas->prodi_id == $request->prodi_id) {
                // script lama
                foreach ($user->getAttributes() as $attribute => $value) {
                    // Hanya perbarui atribut jika datanya kosong di database dan tidak kosong di request
                    if ($value === null && $request->has($attribute)) {
                        $user->{$attribute} = $request->{$attribute};
                    }
                }
                // Simpan perubahan jika ada yang diubah
                if ($user->isDirty()) {
                    $user->save();
                    return redirect()->route('home.profilshow', $user->id)->with('success', 'Profil berhasil diperbarui.');
                } else {
                    return redirect()->back()->with('error', 'AKSES DITOLAK! Hubungi admin untuk melakukan perubahan data.');
                }
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
            Storage::delete('/public/images/' . $user->image);
        }

        // Upload file foto baru ke storage
        $file = $request->file('foto-profil');
        $filename = Str::random(10) . '-' . $file->getClientOriginalName();
        $file->storeAs('images', $filename, 'public');

        // Perbarui URL foto di database
        $user->image = $filename;
    }

    public function editProfileGmail(Request $request, $id)
    {
        try {
            $request->validate([
                'no_hp' => 'required|numeric|digits_between:10,13|unique:users,no_hp,' . $id,
                'email' => [
                    'required',
                    'email:dns',
                    'unique:users,email,' . $id,
                ],
                'password' => 'min:5|nullable',
            ]);

            $user = User::find($id);
            // dd($user);
            $existingUser = User::where('no_hp', $request->no_hp)->first();
            if ($existingUser && $existingUser->id != $user->id) {
                return redirect()->back()->with('error', 'Nomor HP sudah digunakan oleh pengguna lain.');
            }

            if (!$user) {
                return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
            }

            $user->no_hp = $request->no_hp;
            $user->email = $request->email;
            // jika password nullable, gunakan password lama
            $user->password = Hash::make($request->password);
            $user->save();

            if ($user->isAdmin() || $user->isOperator() || $user->isPelatih()) {
                return redirect()->route('admin.profil', $user->id)->with('success-email', 'Informasi akun berhasil diperbarui.');
            } else {
                return redirect()->route('home.profilshow', $user->id)->with('success-email', 'Informasi akun berhasil diperbarui.');
            }
        } catch (\Exception $e) {
            $user = User::find($id);
            if ($user->isAdmin() || $user->isOperator() || $user->isPelatih()) {
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
            Storage::delete('/public/images/' . $user->image);
            $user->image = null;
            $user->save();
            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Foto profil tidak ditemukan.');
        }
    }
}
