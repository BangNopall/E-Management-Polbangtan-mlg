<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user->role_id == \App\Models\User::USER_ROLE_ID) {
                $isIncomplete = false;

                // Check essential profile fields
                if (empty($user->no_hp) || empty($user->kelas_id) || empty($user->blok_ruangan_id) || empty($user->no_kamar) || empty($user->asal_daerah)) {
                    $isIncomplete = true;
                }

                // Check dummy email
                if (str_ends_with($user->email, '@ganti.email')) {
                    $isIncomplete = true;
                }

                // Check default password without running Bcrypt on every request
                if (! $user->is_password_changed) {
                    if (\Illuminate\Support\Facades\Hash::check('password', $user->password)) {
                        $isIncomplete = true;
                    } else {
                        // Automatically mark existing users with custom password as changed
                        $user->is_password_changed = true;
                        $user->save();
                    }
                }

                // To prevent redirect loop, check if current route is profile edit
                $allowedRoutes = ['home.profilshow', 'home.Editprofil', 'home.EditprofilGmail', 'home.deleteFotoProfile', 'auth.logout', 'user.profil', 'auth.dashboard'];
                if ($isIncomplete && !in_array($request->route()?->getName(), $allowedRoutes)) {
                    return redirect()->route('home.profilshow')
                        ->with('error', 'Untuk melanjutkan, lengkapi seluruh data profil, ubah email dari default, dan ganti password Anda terlebih dahulu.');
                }
            } else {
                // Staff / Admin / Operator / Dosen PA / Pejabat / Pelatih / Pembina
                if (! $user->is_password_changed) {
                    if (\Illuminate\Support\Facades\Hash::check('password', $user->password)) {
                        $allowedAdminRoutes = ['admin.profil', 'admin.editProfile', 'admin.editProfileGmail', 'admin.deleteFotoProfile', 'admin.deleteFotoProfileMahasiswa', 'auth.logout', 'user.profil', 'auth.dashboard'];
                        if (!in_array($request->route()?->getName(), $allowedAdminRoutes)) {
                            return redirect()->route('admin.profil', $user->id)
                                ->with('error', 'Untuk keamanan akun, Anda wajib mengubah password default terlebih dahulu.');
                        }
                    } else {
                        $user->is_password_changed = true;
                        $user->save();
                    }
                }
            }
        }

        return $next($request);
    }
}
