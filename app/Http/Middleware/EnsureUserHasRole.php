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
        $userRole = Role::find(auth()->user()->role_id);
        foreach ($roles as $role) {
            // if ($role === "superadmin" && auth()->user()->isSuperadmin()) return $next($request);
            if ($userRole->name === $role) {
                return $next($request);
            }
        }

        // return abort(403);
        $route  = $userRole->name === 'user' ? 'home.index' : 'admin.index';
        return redirect()->route($route)->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
