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
        if (auth()->check() && auth()->user()->role_id == 3) {
            $user = auth()->user();
            
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
            $allowedRoutes = ['home.profilshow', 'home.Editprofil', 'home.EditprofilGmail', 'home.deleteFotoProfile', 'auth.logout'];
            if ($isIncomplete && !in_array($request->route()->getName(), $allowedRoutes)) {
                return redirect()->route('home.profilshow')
                    ->with('error', 'Untuk melanjutkan, lengkapi seluruh data profil, ubah email dari default, dan ganti password Anda terlebih dahulu.');
            }
        }

        return $next($request);
    }
}
