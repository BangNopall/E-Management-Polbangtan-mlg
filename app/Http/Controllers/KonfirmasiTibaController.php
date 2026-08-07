<?php

namespace App\Http\Controllers;

use App\Models\PengajuanIzin;
use App\Models\User;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KonfirmasiTibaController extends Controller
{
    /**
     * Halaman Publik Konfirmasi Kedatangan (Signed URL).
     * Jika tiba_at SUDAH terisi -> tampilkan tanda terima statis (tidak dapat diisi ulang).
     */
    public function show(Request $request, string $qr_token)
    {
        $pengajuan = PengajuanIzin::with(['user', 'jenisIzin', 'ukm'])
            ->where('qr_token', $qr_token)
            ->firstOrFail();

        return view('publik.konfirmasi-tiba', compact('pengajuan'));
    }

    /**
     * Simpan data konfirmasi tiba di tempat tujuan eksternal.
     */
    public function store(Request $request, string $qr_token)
    {
        $pengajuan = PengajuanIzin::where('qr_token', $qr_token)->firstOrFail();

        // Sekali pakai: jika sudah dikonfirmasi, tolak submission ulang
        if ($pengajuan->tiba_at !== null) {
            return redirect()->to($request->fullUrl())
                ->with('info', 'Konfirmasi kedatangan ini telah tersimpan sebelumnya.');
        }

        $request->validate([
            'tiba_dikonfirmasi_oleh' => ['required', 'string', 'max:255'],
            'tiba_di' => ['required', 'string', 'max:255'],
            'tiba_kontak' => ['nullable', 'string', 'max:50'],
        ], [
            'tiba_dikonfirmasi_oleh.required' => 'Nama pemeriksa / pengonfirmasi (RT/Panitia/Orang Tua) wajib diisi.',
            'tiba_di.required' => 'Lokasi kedatangan (Kota/Desa) wajib diisi.',
        ]);

        $now = Carbon::now();

        $pengajuan->update([
            'tiba_at' => $now,
            'tiba_di' => $request->input('tiba_di'),
            'tiba_dikonfirmasi_oleh' => $request->input('tiba_dikonfirmasi_oleh'),
            'tiba_kontak' => $request->input('tiba_kontak'),
        ]);

        // Kirim Notifikasi In-App ke Admin / Pengurus Asrama
        $admins = User::where('role_id', 1)->get();
        $notifikasiService = app(NotifikasiService::class);

        foreach ($admins as $admin) {
            $notifikasiService->kirim(
                $admin->id,
                'Konfirmasi Tiba Mahasiswa',
                "Mahasiswa {$pengajuan->nama_snapshot} dikonfirmasi telah tiba di {$pengajuan->tiba_di} oleh {$pengajuan->tiba_dikonfirmasi_oleh}.",
                route('admin.izin.persetujuan.review', $pengajuan->id)
            );
        }

        return redirect()->to($request->fullUrl())
            ->with('success', 'Konfirmasi kedatangan berhasil dikirimkan. Terima kasih.');
    }
}
