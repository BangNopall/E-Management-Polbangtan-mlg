<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\LoginPermission;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    public function authenticate(Request $request)
    {
        try {
            // Validasi input email dan password
            $credentials = $request->validate([
                'email' => 'required|email:dns',
                'password' => 'required',
            ]);

            // Temukan pengguna berdasarkan alamat email
            $user = User::where('email', $credentials['email'])->first();

            // checl password 
            if (!Hash::check($credentials['password'], $user->password)) {
                return back()->with('error', 'Login gagal, silahkan cek email dan password Anda!');
            }

            if (!$user) {
                return back()->with('error', 'Login gagal, user tidak ditemukan!');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Login gagal, silahkan cek email dan password Anda!');
        }

        try {
            if ($user->role_id == 3) {
                $LoginPermission = LoginPermission::firstOrNew(['user_id' => $user->id]);
                if (!$LoginPermission->exists) {
                    $LoginPermission->is_login = 1;
                    $LoginPermission->expiry_date = now()->startOfMonth()->addYears(1);
                    $LoginPermission->save();

                    if (Auth::attempt($credentials)) {
                        $user = auth()->user();
                        return redirect()->route('home.index');
                    }
                } else {
                    if (Auth::attempt($credentials)) {
                        $user = auth()->user();
                        return redirect()->route('home.index');
                    }
                }
            }

            // Jika pengguna memiliki role_id 1 atau 2 (Admin)
            if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4) {
                // Coba otentikasi pengguna dan arahkan ke halaman admin jika berhasil
                if (Auth::attempt($credentials)) {
                    $user = auth()->user();
                    return redirect()->route('admin.index');
                }
            }

            // // Jika otentikasi gagal, kembalikan pesan kesalahan
            return back()->with('error', 'Login gagal, silahkan cek email dan password Anda!');
        } catch (\Exception $e) {
            return back()->with('error', 'Login gagal, silahkan cek email dan password Anda!');
        }
    }


    public function LogoutAccount()
    {
        $user = auth()->user();

        if ($user->role_id == 3) {
            // Periksa apakah ada entri aktif untuk pengguna ini dalam tabel login_monthlies
            $LoginPermission = LoginPermission::where('user_id', $user->id)
                ->where('is_login', true)
                ->first();

            // Jika ada, atur is_login menjadi true
            if ($LoginPermission) {
                $LoginPermission->is_login = true;
                $LoginPermission->is_logout = true;
                $LoginPermission->desc_logout = 'Siswa melakukan Logout secara paksa dari perangkat pada tanggal ' . now()->format('d-m-Y') . '';
                $LoginPermission->save();
            }

            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            request()->session()->flush(); // Hapus semua data sesi

            return redirect()->route('auth.login')->with('success', 'Anda berhasil keluar.');
        }
        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4) {
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            request()->session()->flush(); // Hapus semua data sesi

            return redirect()->route('auth.login')->with('success', 'Anda berhasil keluar.');
        }
    }

    public function authDashboard()
    {
        $user = auth()->user();
        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4) {
            return redirect()->route('admin.index');
        }
        if ($user->role_id == 3) {
            return redirect()->route('home.index');
        }
    }
}
