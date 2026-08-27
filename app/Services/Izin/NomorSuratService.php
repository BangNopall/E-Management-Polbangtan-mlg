<?php

namespace App\Services\Izin;

use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    /**
     * Dapatkan nomor surat resmi berikutnya secara atomic dan thread-safe.
     * Format: {urut_harian}/{tanggal_2_digit}/{bulan_2_digit}/{tahun}
     * Contoh: 1/24/08/2026
     */
    public function generateNext(Carbon $date): string
    {
        return DB::transaction(function () use ($date) {
            $year = $date->year;
            $month = str_pad((string)$date->month, 2, '0', STR_PAD_LEFT);
            $day = str_pad((string)$date->day, 2, '0', STR_PAD_LEFT);

            // Hitung nomor urut penerbitan surat dalam HARI berjalan
            // Penguncian lockForUpdate() memastikan pencegahan race condition saat penerbitan paralel
            $countToday = PengajuanIzin::whereDate('created_at', $date->toDateString())
                ->whereNotNull('nomor_surat')
                ->lockForUpdate()
                ->count();

            $sequence = $countToday + 1;

            return "{$sequence}/{$day}/{$month}/{$year}";
        });
    }
}
