<?php

namespace App\Services\Izin;

use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NomorSuratService
{
    /**
     * Dapatkan nomor surat resmi berikutnya secara atomic dan thread-safe.
     * Format: AR.009/{urut_3_digit}/{romawi_bulan}/{tahun}
     * Contoh: AR.009/001/VIII/2026
     */
    public function generateNext(Carbon $date): string
    {
        return DB::transaction(function () use ($date) {
            $year = $date->year;
            $month = $date->month;

            // Hitung nomor urut penerbitan surat dalam tahun berjalan
            // Penguncian lockForUpdate() memastikan pencegahan race condition saat penerbitan paralel
            $countThisYear = PengajuanIzin::whereYear('disetujui_at', $year)
                ->whereNotNull('nomor_surat')
                ->lockForUpdate()
                ->count();

            $sequence = $countThisYear + 1;
            $paddedSeq = str_pad((string)$sequence, 3, '0', STR_PAD_LEFT);
            $romanMonth = $this->toRoman($month);

            return "AR.009/{$paddedSeq}/{$romanMonth}/{$year}";
        });
    }

    /**
     * Helper konversi angka bulan (1-12) ke huruf Romawi.
     */
    protected function toRoman(int $month): string
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $map[$month] ?? 'I';
    }
}
