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
}
