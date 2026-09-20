<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePejabatReadOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isPejabat()) {
            // Define destructive GET routes that should be blocked for Pejabat
            $blockedGetRoutes = [
                'admin.deleteJenisPelanggaran',
                'admin.deleteKategori',
                'admin.laporanPelanggaranDeleted',
                'admin.laporanPelanggaranDone',
            ];

            if ($request->isMethod('GET') && in_array($request->route()->getName(), $blockedGetRoutes)) {
                return redirect()->back()->with('error', 'Akses ditolak: Role Pejabat hanya memiliki akses Read-Only pada modul ini.');
            }

            if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
                // Allow logout
                if ($request->routeIs('auth.logout')) {
                    return $next($request);
                }

                // Allow perizinan routes and presensi (kamera scan)
                if ($request->routeIs('izin.*') || $request->routeIs('admin.izin.*') || $request->routeIs('jenis.*') || $request->routeIs('admin.jenis.*') || $request->routeIs('publik.*') || $request->is('*/izin/*') || $request->is('*/jenis/*') || $request->routeIs('admin.presense.api')) {
                    return $next($request);
                }

                // Otherwise, abort with error
                return redirect()->back()->with('error', 'Akses ditolak: Role Pejabat hanya memiliki akses Read-Only pada modul ini.');
            }
        }

        return $next($request);
    }
}
