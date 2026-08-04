<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUkmJadwalRequest;
use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmPresensi;
use Illuminate\Http\RedirectResponse;
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

            foreach ($activeMembers as $member) {
                UkmPresensi::firstOrCreate([
                    'ukm_jadwal_id' => $jadwal->id,
                    'user_id' => $member->user_id,
                ], [
                    'status_kehadiran' => 'Alpha',
                ]);
            }
        });

        return redirect()->route('admin.ukm.show', $ukm->id)
            ->with('success', 'Jadwal kegiatan UKM berhasil ditambahkan dan presensi anggota telah disiapkan.');
    }
}
