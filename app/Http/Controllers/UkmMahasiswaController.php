<?php

namespace App\Http\Controllers;

use App\Models\UkmMember;
use App\Models\UkmPresensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UkmMahasiswaController extends Controller
{
    /**
     * Display "UKM Saya" dashboard for students (Role 3).
     * Lists active UKM memberships and upcoming schedules.
     */
    public function index(): View
    {
        $user = Auth::user();

        $memberships = UkmMember::where('user_id', $user->id)
            ->where('status', 'aktif')
            ->with(['ukm.jadwals' => function ($query) {
                $query->where('tanggal', '>=', now()->toDateString())
                    ->orderBy('tanggal', 'asc')
                    ->limit(5);
            }])
            ->get();

        return view('ukm.index', compact('memberships'));
    }

    /**
     * Display UKM presence history for the authenticated student.
     */
    public function riwayat(): View
    {
        $user = Auth::user();

        $presensis = UkmPresensi::where('user_id', $user->id)
            ->with(['jadwal.ukm'])
            ->latest()
            ->paginate(15);

        return view('ukm.riwayat', compact('presensis'));
    }
}
