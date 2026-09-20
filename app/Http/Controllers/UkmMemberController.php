<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUkmMemberRequest;
use App\Models\Ukm;
use App\Models\UkmMember;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UkmMemberController extends Controller
{
    protected function ensureUserCanManageUkm($ukmId)
    {
        $user = Auth::user();
        if ($user->role_id !== User::ADMIN_ROLE_ID) {
            $isStaff = UkmMember::where('ukm_id', $ukmId)
                ->where('user_id', $user->id)
                ->whereIn('peran', ['pelatih', 'pembina'])
                ->where('status', 'aktif')
                ->exists();
            abort_unless($isStaff, 403, 'Anda tidak berhak mengelola anggota pada UKM ini.');
        }
    }

    public function store(StoreUkmMemberRequest $request, $ukmId)
    {
        $this->ensureUserCanManageUkm($ukmId);
        try {
            $ukm = Ukm::findOrFail($ukmId);
            $validated = $request->validated();

            $member = $ukm->members()->withTrashed()->where('user_id', $validated['user_id'])->first();

            if ($member) {
                // Restore if soft-deleted
                if ($member->trashed()) {
                    $member->restore();
                }
                $member->update([
                    'peran' => $validated['peran'],
                    'status' => 'aktif',
                    'tanggal_bergabung' => now(),
                ]);
            } else {
                $ukm->members()->create([
                    'user_id' => $validated['user_id'],
                    'peran' => $validated['peran'],
                    'status' => 'aktif',
                    'tanggal_bergabung' => now(),
                ]);
            }

            return redirect()->back()->with('success', 'Anggota berhasil ditambahkan ke ' . $ukm->nama);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy($ukmId, $memberId)
    {
        $this->ensureUserCanManageUkm($ukmId);
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
        $this->ensureUserCanManageUkm($ukmId);
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
        $this->ensureUserCanManageUkm($ukmId);
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
