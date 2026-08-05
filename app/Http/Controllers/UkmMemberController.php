<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUkmMemberRequest;
use App\Models\Ukm;
use App\Models\UkmMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UkmMemberController extends Controller
{
    public function store(StoreUkmMemberRequest $request, $ukmId)
    {
        try {
            $ukm = Ukm::findOrFail($ukmId);
            $validated = $request->validated();

            $ukm->members()->create([
                'user_id' => $validated['user_id'],
                'peran' => $validated['peran'],
                'status' => 'aktif',
                'tanggal_bergabung' => now(),
            ]);

            return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke ' . $ukm->nama);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy($ukmId, $memberId)
    {
        try {
            $member = UkmMember::where('ukm_id', $ukmId)->where('id', $memberId)->firstOrFail();
            $member->delete();

            return redirect()->back()->with('success', 'Anggota berhasil dikeluarkan dari UKM.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Penyempurnaan Alur UKM — Isu #1.
     * Reaktivasi 1 anggota yang berstatus 'nonaktif'. HANYA diperbolehkan jika
     * UKM induknya berstatus aktif (is_active = true) — sesuai keputusan user,
     * anggota tidak boleh diaktifkan jika UKM-nya masih mati.
     */
    public function aktifkan($ukmId, $memberId)
    {
        try {
            $ukm = Ukm::findOrFail($ukmId);
            if (! $ukm->is_active) {
                return redirect()->back()->with('error', 'Tidak dapat mengaktifkan anggota karena UKM ' . $ukm->nama . ' sedang nonaktif. Aktifkan UKM terlebih dahulu.');
            }

            $member = UkmMember::where('ukm_id', $ukmId)->where('id', $memberId)->firstOrFail();
            $member->update(['status' => 'aktif']);

            return redirect()->back()->with('success', 'Status keanggotaan berhasil diaktifkan kembali.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    /**
     * Penyempurnaan Alur UKM — Isu #1.
     * Reaktivasi massal seluruh anggota 'nonaktif' di UKM ini sekaligus.
     * HANYA diperbolehkan jika UKM induknya berstatus aktif.
     */
    public function aktifkanSemua($ukmId)
    {
        try {
            $ukm = Ukm::findOrFail($ukmId);
            if (! $ukm->is_active) {
                return redirect()->back()->with('error', 'Tidak dapat mengaktifkan anggota karena UKM ' . $ukm->nama . ' sedang nonaktif. Aktifkan UKM terlebih dahulu.');
            }

            $jumlah = $ukm->members()->where('status', 'nonaktif')->update(['status' => 'aktif']);

            return redirect()->back()->with('success', $jumlah . ' anggota berhasil diaktifkan kembali.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}
