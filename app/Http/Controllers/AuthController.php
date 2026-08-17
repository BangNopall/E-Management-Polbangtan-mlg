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
                'email' => 'required',
                'password' => 'required',
            ]);

            // Temukan pengguna berdasarkan alamat email atau nim
            $user = User::where('email', $credentials['email'])
                        ->orWhere('nim', $credentials['email'])
                        ->first();

            if (!$user) {
                return back()->with('error', 'Login gagal, user tidak ditemukan!');
            }

            // checl password 
            if (!Hash::check($credentials['password'], $user->password)) {
                return back()->with('error', 'Login gagal, silahkan cek email/nim dan password Anda!');
            }
            
            // Set correct email for Auth::attempt
            $credentials['email'] = $user->email;

        } catch (\Exception $e) {
            return back()->with('error', 'Login gagal, silahkan cek email/nim dan password Anda!');
        }

        try {
            if ($user->role_id == 3) {
                $LoginPermission = LoginPermission::firstOrNew(['user_id' => $user->id]);
                
                if ($LoginPermission->exists && $LoginPermission->is_login == 1) {
                    // Cek jika belum expire
                    if (now()->lessThan($LoginPermission->expiry_date)) {
                        return back()->with('error', 'Akun sedang digunakan di perangkat lain. Silahkan logout dari perangkat sebelumnya.');
                    }
                }

                $LoginPermission->is_login = 1;
                $LoginPermission->is_logout = 0;
                $LoginPermission->expiry_date = now()->addHours(12); // Kadaluarsa sesi 12 jam agar tidak stuck permanen jika lupa logout
                $LoginPermission->save();

                if (Auth::attempt($credentials)) {
                    return redirect()->route('home.index');
                }
            }

            // Jika pengguna memiliki role_id 1, 2, 4, atau 5 (staf: admin/operator/pelatih/pembina)
            if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4 || $user->role_id == User::PEMBINA_ROLE_ID) {
                // Coba otentikasi pengguna dan arahkan ke halaman admin jika berhasil
                if (Auth::attempt($credentials)) {
                    $user = auth()->user();
                    return redirect()->route('admin.index');
                }
            }

            // // Jika otentikasi gagal, kembalikan pesan kesalahan
            return back()->with('error', 'Login gagal, silahkan cek email/nim dan password Anda!');
        } catch (\Exception $e) {
            return back()->with('error', 'Login gagal, silahkan cek email/nim dan password Anda!');
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

            // Jika ada, atur is_login menjadi false
            if ($LoginPermission) {
                $LoginPermission->is_login = 0;
                $LoginPermission->is_logout = 1;
                $LoginPermission->desc_logout = 'Siswa melakukan Logout secara mandiri dari perangkat pada tanggal ' . now()->format('d-m-Y H:i') . '';
                $LoginPermission->save();
            }

            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            request()->session()->flush(); // Hapus semua data sesi

            return redirect()->route('auth.login')->with('success', 'Anda berhasil keluar.');
        }
        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4 || $user->role_id == User::PEMBINA_ROLE_ID) {
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
        if ($user->role_id == 1 || $user->role_id == 2 || $user->role_id == 4 || $user->role_id == User::PEMBINA_ROLE_ID) {
            return redirect()->route('admin.index');
        }
        if ($user->role_id == 3) {
            return redirect()->route('home.index');
        }
    }
}
