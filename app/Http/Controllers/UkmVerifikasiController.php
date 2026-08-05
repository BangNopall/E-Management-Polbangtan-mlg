<?php

namespace App\Http\Controllers;

use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UkmVerifikasiController extends Controller
{
    /**
     * Display a listing of schedules pending verification for a specific UKM.
     * Accessible by Admin (1) and assigned Pembina (5) for this UKM.
     */
    public function index(Ukm $ukm): View
    {
        $user = Auth::user();

        // Scope check: Admin (1) can access any UKM, Pembina (5) must be registered as pembina of this UKM
        if ($user->role_id !== User::ADMIN_ROLE_ID) {
            $isPembinaOfThisUkm = UkmMember::where('ukm_id', $ukm->id)
                ->where('user_id', $user->id)
                ->where('peran', 'pembina')
                ->where('status', 'aktif')
                ->exists();

            abort_unless($isPembinaOfThisUkm, 403, 'Anda tidak berhak memverifikasi UKM ini.');
        }

        // Isu #3: jadwal 'draft' belum diajukan Pelatih, jadi tidak boleh masuk
        // antrian Pembina. Alur wajib: draft -> (Ajukan Verifikasi) -> menunggu
        // -> disetujui/ditolak.
        $jadwals = UkmJadwal::where('ukm_id', $ukm->id)
            ->where('status_verifikasi', '!=', 'draft')
            ->with(['presensis.user', 'verifier'])
            ->latest('tanggal')
            ->paginate(20);

        return view('admin.ukm.verifikasi', compact('ukm', 'jadwals'));
    }

    /**
     * Approve or Reject a specific UKM schedule verification request.
     */
    public function update(Request $request, UkmJadwal $jadwal): RedirectResponse
    {
        $user = Auth::user();
        $ukm = $jadwal->ukm;

        if ($user->role_id !== User::ADMIN_ROLE_ID) {
            $isPembinaOfThisUkm = UkmMember::where('ukm_id', $ukm->id)
                ->where('user_id', $user->id)
                ->where('peran', 'pembina')
                ->where('status', 'aktif')
                ->exists();

            abort_unless($isPembinaOfThisUkm, 403, 'Anda tidak berhak memverifikasi UKM ini.');
        }

        // Isu #3: Pembina tidak boleh menyetujui/menolak jadwal yang belum
        // diajukan Pelatih. Ditegakkan di server, bukan sekadar menyembunyikan
        // tombol — form bisa di-submit langsung.
        if ($jadwal->status_verifikasi === 'draft') {
            return redirect()->route('admin.ukm.verifikasi.index', $ukm->id)
                ->with('error', 'Jadwal "' . $jadwal->judul . '" belum diajukan untuk verifikasi oleh Pelatih.');
        }

        $request->validate([
            'status_verifikasi' => 'required|in:disetujui,ditolak',
            'catatan_pembina' => 'nullable|string',
        ]);

        $jadwal->update([
            'status_verifikasi' => $request->input('status_verifikasi'),
            'verified_by' => $user->id,
            'verified_at' => now(),
            'catatan_pembina' => $request->input('catatan_pembina'),
        ]);

        $pesan = $request->input('status_verifikasi') === 'disetujui' ? 'disetujui' : 'ditolak';

        return redirect()->route('admin.ukm.verifikasi.index', $ukm->id)
            ->with('success', 'Jadwal kegiatan UKM berhasil ' . $pesan . '.');
    }
}
