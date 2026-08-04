<?php

namespace App\Http\Controllers;

use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UkmScanController extends Controller
{
    /**
     * Show generic QR camera scanner page for a specific UKM schedule.
     */
    public function show(UkmJadwal $jadwal): View
    {
        $user = Auth::user();
        abort_unless($this->isStaffOfUkm($user, $jadwal->ukm_id), 403, 'Anda tidak berhak membuka scanner UKM ini.');

        $jadwal->load('ukm');

        return view('admin.ukm.kamera-ukm', compact('user', 'jadwal'));
    }

    /**
     * Handle incoming QR scan POST submission for a specific UKM schedule.
     */
    public function store(Request $request, UkmJadwal $jadwal): RedirectResponse
    {
        abort_unless($this->isStaffOfUkm(Auth::user(), $jadwal->ukm_id), 403, 'Anda tidak berhak melakukan scan untuk UKM ini.');

        if ($request->input('scanner') !== 'absensi') {
            return redirect()->route('admin.ukm.scan.show', $jadwal->id)
                ->with('error', 'Anda tidak dapat melakukan scan kode QR Pelanggaran pada scanner kegiatan.');
        }

        try {
            $request->validate([
                'user_id' => 'required',
                'date' => 'required',
                'time' => 'required',
                'scanner' => 'required',
            ]);

            // 1. Time freshness validation (30 seconds window)
            $parsedTime = Carbon::parse($request->input('date') . ' ' . $request->input('time'));
            $timeNow = Carbon::now();
            $timeDifference = abs($timeNow->diffInSeconds($parsedTime));
            $maxDifference = 30;

            if ($timeDifference > $maxDifference) {
                throw new Exception('QR Code Expired');
            }

            // 2. Validate student user exists
            $user = User::find($request->input('user_id'));
            if (! $user) {
                throw new Exception('Mahasiswa tidak ditemukan');
            }

            // 3. Validate student is active member of this UKM
            $isMember = UkmMember::where('ukm_id', $jadwal->ukm_id)
                ->where('user_id', $user->id)
                ->where('peran', 'anggota')
                ->where('status', 'aktif')
                ->exists();

            if (! $isMember) {
                throw new Exception("Mahasiswa {$user->name} bukan anggota aktif UKM {$jadwal->ukm->nama}");
            }

            // 4. Validate schedule window (mulai_acara <= time <= selesai_acara)
            $scanTime = $request->input('time');
            if ($scanTime < $jadwal->mulai_acara) {
                throw new Exception('Kegiatan ' . $jadwal->judul . ' Belum Dimulai');
            }
            if ($scanTime > $jadwal->selesai_acara) {
                throw new Exception('Kegiatan ' . $jadwal->judul . ' Telah Selesai');
            }

            // 5. Update or check presence status
            $presensi = UkmPresensi::where('ukm_jadwal_id', $jadwal->id)
                ->where('user_id', $user->id)
                ->first();

            if ($presensi && $presensi->status_kehadiran === 'Hadir') {
                throw new Exception('Anda Sudah Melakukan Presensi');
            }

            if ($presensi) {
                $presensi->update([
                    'status_kehadiran' => 'Hadir',
                    'jam_kehadiran' => $scanTime,
                ]);
            } else {
                UkmPresensi::create([
                    'ukm_jadwal_id' => $jadwal->id,
                    'user_id' => $user->id,
                    'status_kehadiran' => 'Hadir',
                    'jam_kehadiran' => $scanTime,
                ]);
            }

            return redirect()->route('admin.ukm.scan.show', $jadwal->id)
                ->with('success', 'Presensi UKM untuk Mahasiswa atas nama ' . $user->name . ' Berhasil Dilakukan.');
        } catch (Exception $e) {
            logger()->error('Scan error: ' . $e->getMessage());
            return redirect()->route('admin.ukm.scan.show', $jadwal->id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Admin bypasses the scope check; Pelatih/Pembina must be an active
     * staff member (peran = pelatih|pembina) of the given UKM to operate
     * its scanner — prevents staff of UKM A from scanning for UKM B.
     */
    private function isStaffOfUkm(?User $user, int $ukmId): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->role_id === User::ADMIN_ROLE_ID) {
            return true;
        }

        return UkmMember::where('ukm_id', $ukmId)
            ->where('user_id', $user->id)
            ->whereIn('peran', ['pelatih', 'pembina'])
            ->where('status', 'aktif')
            ->exists();
    }
}
