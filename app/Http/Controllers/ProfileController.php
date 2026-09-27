<?php

namespace App\Http\Controllers;

use App\Models\BlokRuangan;
use App\Models\Kelas;
use App\Models\Prodi;
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

        if (!$user->isUser()) {
            return redirect()->route('admin.profil', $user->id);
        }

        if ($user->isUser()) {
            return redirect()->route('home.profilshow', $user->id);
        }
    }

    public function profil()
    {
        $user = auth()->user();
        $blocks = BlokRuangan::all();
        $prodis = Prodi::all();
        $kelas = Kelas::all();
        $dosenPas = User::where('role_id', User::DOSEN_PA_ROLE_ID)->get();

        $title = 'Profil';

        return view('profil', compact('user', 'blocks', 'prodis', 'kelas', 'title', 'dosenPas'));
    }

    public function editProfile(Request $request, $id)
    {
        abort_unless((int)$id === auth()->id() || auth()->user()->isAdmin(), 403, 'Akses ditolak: Anda tidak memiliki izin mengedit profil pengguna lain.');

        // Temukan pengguna berdasarkan ID
        $user = User::find($id);

        // Periksa apakah pengguna ditemukan
        if (! $user) {
            return redirect()->back()->with('error', 'Pengguna tidak ditemukan.');
        }

        if ($user->isUser()) {
            $rules = [
                'name' => 'required|string|max:255',
                'kelas_id' => 'required|exists:kelas,id',
                'blok_ruangan_id' => 'required|exists:blok_ruangans,id',
                'no_kamar' => 'required|numeric',
                'asal_daerah' => 'required|string|max:255',
                'foto-profil' => 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:10250',
                'dosen_pa_id' => 'nullable|exists:users,id',
            ];
        } else {
            $rules = [
                'name' => 'required|string|max:255',
                'foto-profil' => 'nullable|image|mimes:jpeg,png,jpg,webp,heic|max:10250',
                'nim' => 'nullable|numeric|min:11|unique:users,nim,'.$id,
                'prodi_id' => 'nullable',
                'kelas_id' => 'nullable|exists:kelas,id',
                'blok_ruangan_id' => 'nullable|exists:blok_ruangans,id',
                'no_kamar' => 'nullable|numeric',
                'asal_daerah' => 'nullable|string|max:255',
                'dosen_pa_id' => 'nullable|exists:users,id',
            ];
        }

        // Validasi data dari formulir
        $request->validate($rules);

        if (!$user->isUser() && $request->filled('nim')) {
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
        if (!$user->isUser()) {
            $user->name = $request->name;
            if ($request->filled('nim')) $user->nim = $request->nim;
            if ($request->filled('prodi_id')) $user->prodi_id = $request->prodi_id;
            if ($request->filled('kelas_id')) $user->kelas_id = $request->kelas_id;
            if ($request->filled('blok_ruangan_id')) $user->blok_ruangan_id = $request->blok_ruangan_id;
            if ($request->filled('no_kamar')) $user->no_kamar = $request->no_kamar;
            if ($request->filled('asal_daerah')) $user->asal_daerah = $request->asal_daerah;
            if ($request->filled('dosen_pa_id')) $user->dosen_pa_id = $request->dosen_pa_id;
            $user->save();

            return redirect()->route('admin.profil')->with('success', 'Profil berhasil diperbarui.');
        } elseif ($user->isUser()) {
            // script baru
            $cariKelas = Kelas::where('id', $request->kelas_id)->first();
            if ($cariKelas && $cariKelas->prodi_id == $user->prodi_id) { // Compare with existing prodi_id
                // Do not update NIM and prodi_id
                $user->name = $request->name;
                $user->kelas_id = $request->kelas_id;
                $user->blok_ruangan_id = $request->blok_ruangan_id;
                $user->no_kamar = $request->no_kamar;
                $user->asal_daerah = $request->asal_daerah;
                if ($request->has('dosen_pa_id')) $user->dosen_pa_id = $request->dosen_pa_id;
                $user->save();

                return redirect()->route('home.profilshow')->with('success', 'Profil berhasil diperbarui.');
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
        abort_unless((int)$id === auth()->id() || auth()->user()->isAdmin(), 403, 'Akses ditolak: Anda tidak memiliki izin mengubah informasi akun pengguna lain.');

        try {
            $user = User::findOrFail($id);

            $rules = [
                'email' => [
                    'required',
                    'email',
                    'unique:users,email,'.$id,
                ],
                'password' => 'min:5|nullable',
            ];

            if ($user->isUser()) {
                $rules['no_hp'] = 'required|numeric|digits_between:10,13|unique:users,no_hp,'.$id;
            } else {
                $rules['no_hp'] = 'nullable|numeric|digits_between:10,13|unique:users,no_hp,'.$id;
            }

            $request->validate($rules);

            if ($request->filled('no_hp')) {
                $existingUser = User::where('no_hp', $request->no_hp)->first();
                if ($existingUser && $existingUser->id != $user->id) {
                    return redirect()->back()->with('error', 'Nomor HP sudah digunakan oleh pengguna lain.');
                }
                $user->no_hp = $request->no_hp;
            }

            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
                $user->is_password_changed = true;
            }

            $user->save();

            if (!$user->isUser()) {
                return redirect()->route('admin.profil')->with('success-email', 'Informasi akun berhasil diperbarui.');
            } else {
                return redirect()->route('home.profilshow')->with('success-email', 'Informasi akun berhasil diperbarui.');
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            $user = User::find($id);
            if (!$user || !$user->isUser()) {
                return redirect()->route('admin.profil')->with('error-email', 'Informasi akun Gagal diperbarui.');
            } else {
                return redirect()->route('home.profilshow')->with('error-email', 'Informasi akun Gagal diperbarui.');
            }
        }
    }

    public function deleteFotoProfile($id)
    {
        abort_unless((int)$id === auth()->id() || auth()->user()->isAdmin(), 403, 'Akses ditolak: Anda tidak memiliki izin menghapus foto profil pengguna lain.');

        $user = User::find($id);
        if ($user && $user->image) {
            Storage::delete('/public/images/'.$user->image);
            $user->image = null;
            $user->save();

            return redirect()->back()->with('success', 'Foto profil berhasil dihapus.');
        } else {
            return redirect()->back()->with('error', 'Foto profil tidak ditemukan.');
        }
    }
}
