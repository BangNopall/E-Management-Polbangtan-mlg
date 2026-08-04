<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUkmJadwalRequest;
use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UkmJadwalController extends Controller
{
    /**
     * Store a newly created schedule and fan-out presensi for active student members.
     * Everything is wrapped in DB::transaction for consistency and atomicity.
     */
    public function store(StoreUkmJadwalRequest $request, Ukm $ukm): RedirectResponse
    {
        DB::transaction(function () use ($request, $ukm) {
            $jadwal = UkmJadwal::create([
                'ukm_id' => $ukm->id,
                'judul' => $request->validated('judul'),
                'jenis' => $request->validated('jenis'),
                'tanggal' => $request->validated('tanggal'),
                'mulai_acara' => $request->validated('mulai_acara'),
                'selesai_acara' => $request->validated('selesai_acara'),
                'lokasi' => $request->validated('lokasi'),
                'status_verifikasi' => 'draft',
                'created_by' => auth()->id(),
            ]);

            // Fan-out presensi status 'Alpha' only for active student members (peran = 'anggota' & status = 'aktif')
            $activeMembers = $ukm->anggotaAktif()->get();
            $now = now();

            $presensiData = $activeMembers->map(function ($member) use ($jadwal, $now) {
                return [
                    'ukm_jadwal_id' => $jadwal->id,
                    'user_id' => $member->user_id,
                    'status_kehadiran' => 'Alpha',
                    'jam_kehadiran' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray();

            if (! empty($presensiData)) {
                UkmPresensi::insert($presensiData);
            }
        });

        return redirect()->route('admin.ukm.show', $ukm->id)
            ->with('success', 'Jadwal kegiatan UKM berhasil ditambahkan dan presensi anggota telah disiapkan.');
    }

    /**
     * Move a schedule from 'draft' to 'menunggu' so it appears in the
     * Pembina verification queue (UkmVerifikasiController::index).
     * Scoped to Admin or the active pelatih of the schedule's UKM.
     */
    public function ajukanVerifikasi(UkmJadwal $jadwal): RedirectResponse
    {
        $user = Auth::user();

        if ($user->role_id !== User::ADMIN_ROLE_ID) {
            $isPelatihOfThisUkm = UkmMember::where('ukm_id', $jadwal->ukm_id)
                ->where('user_id', $user->id)
                ->where('peran', 'pelatih')
                ->where('status', 'aktif')
                ->exists();

            abort_unless($isPelatihOfThisUkm, 403, 'Anda tidak berhak mengajukan verifikasi jadwal ini.');
        }

        if ($jadwal->status_verifikasi !== 'draft') {
            return redirect()->route('admin.ukm.show', $jadwal->ukm_id)
                ->with('error', 'Jadwal ini sudah diajukan atau sudah diverifikasi sebelumnya.');
        }

        $jadwal->update(['status_verifikasi' => 'menunggu']);

        return redirect()->route('admin.ukm.show', $jadwal->ukm_id)
            ->with('success', 'Jadwal kegiatan berhasil diajukan untuk verifikasi Pembina.');
    }
}
