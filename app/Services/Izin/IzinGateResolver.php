<?php

namespace App\Services\Izin;

use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;

class IzinGateResolver
{
    /**
     * Cari izin aktif milik mahasiswa pada waktu $now (termasuk jendela toleransi -2j / +6j).
     * Sesuai spesifikasi §4 SystemFlow:
     * - Toleransi berangkat awal: waktu_berangkat - 2 jam
     * - Toleransi kembali telat: waktu_kembali + 6 jam
     */
    public function aktifUntuk(User|int $user, ?Carbon $now = null): ?PengajuanIzin
    {
        $userId = $user instanceof User ? $user->id : $user;
        $currentTime = $now ?? Carbon::now();

        return PengajuanIzin::where('user_id', $userId)
            ->whereIn('status', ['disetujui', 'berjalan'])
            ->where(function ($query) use ($currentTime) {
                $query->whereRaw('? >= DATE_SUB(waktu_berangkat, INTERVAL 2 HOUR)', [$currentTime->toDateTimeString()])
                      ->whereRaw('? <= DATE_ADD(waktu_kembali, INTERVAL 6 HOUR)', [$currentTime->toDateTimeString()]);
            })
            ->orderBy('waktu_berangkat', 'asc')
            ->first();
    }
}
