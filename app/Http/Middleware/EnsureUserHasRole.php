<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;

class EnsureUserHasRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        if (! $user) {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }
            return redirect()->route('auth.login');
        }

        $userRole = $user->role ?? ($user->role_id ? Role::find($user->role_id) : null);
        $roleName = $userRole?->name;

        if ($roleName && in_array($roleName, $roles)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        $route = $roleName === 'user' ? 'home.index' : 'admin.index';
        return redirect()->route($route)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
