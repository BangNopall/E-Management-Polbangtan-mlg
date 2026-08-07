<?php

namespace App\Services\Izin;

use App\Models\jadwalKegiatanAsrama;
use App\Models\PengajuanIzin;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\PresensiUpacara;
use App\Models\UkmJadwal;
use App\Models\UkmPresensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PembebasanPresensiService
{
    /**
     * Bebaskan presensi untuk pengajuan izin yang disetujui.
     * Mengubah status presensi beririsan dari 'Alpha' menjadi 'Izin'.
     * JANGAN PERNAH menimpa status 'Hadir'.
     */
    public function bebaskanUntukPengajuan(PengajuanIzin $izin): void
    {
        if (!in_array($izin->status, ['disetujui', 'berjalan', 'selesai', 'terlambat'])) {
            return;
        }

        $userId = $izin->user_id;
        $start = $izin->waktu_berangkat;
        $end = $izin->waktu_kembali;

        DB::transaction(function () use ($userId, $start, $end) {
            // 1. Kegiatan Wajib (Apel, Senam, Upacara)
            $jadwalWajibIds = jadwalKegiatanAsrama::whereBetween('tanggal_kegiatan', [$start->toDateString(), $end->toDateString()])
                ->pluck('id');

            if ($jadwalWajibIds->isNotEmpty()) {
                PresensiApel::whereIn('jadwalKegiatanAsrama_id', $jadwalWajibIds)
                    ->where('user_id', $userId)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);

                PresensiSenam::whereIn('jadwalKegiatanAsrama_id', $jadwalWajibIds)
                    ->where('user_id', $userId)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);

                PresensiUpacara::whereIn('jadwalKegiatanAsrama_id', $jadwalWajibIds)
                    ->where('user_id', $userId)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);
            }

            // 2. UKM Presensi
            $ukmJadwalIds = UkmJadwal::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
                ->pluck('id');

            if ($ukmJadwalIds->isNotEmpty()) {
                UkmPresensi::whereIn('ukm_jadwal_id', $ukmJadwalIds)
                    ->where('user_id', $userId)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);
            }
        });
    }

    /**
     * Bebaskan presensi saat jadwal UKM baru dibuat dan di-fan-out.
     */
    public function bebaskanUntukJadwalUkm(UkmJadwal $jadwal): void
    {
        $tanggalJadwal = Carbon::parse($jadwal->tanggal)->toDateString();

        // Cari semua mahasiswa yang punya izin aktif/disetujui pada tanggal jadwal ini
        $izins = PengajuanIzin::whereIn('status', ['disetujui', 'berjalan'])
            ->whereDate('waktu_berangkat', '<=', $tanggalJadwal)
            ->whereDate('waktu_kembali', '>=', $tanggalJadwal)
            ->get();

        foreach ($izins as $izin) {
            UkmPresensi::where('ukm_jadwal_id', $jadwal->id)
                ->where('user_id', $izin->user_id)
                ->where('status_kehadiran', 'Alpha')
                ->update(['status_kehadiran' => 'Izin']);
        }
    }

    /**
     * Bebaskan presensi saat jadwal kegiatan wajib baru dibuat dan di-fan-out.
     */
    public function bebaskanUntukJadwalKegiatan(jadwalKegiatanAsrama $jadwal): void
    {
        $tanggalJadwal = Carbon::parse($jadwal->tanggal_kegiatan)->toDateString();

        $izins = PengajuanIzin::whereIn('status', ['disetujui', 'berjalan'])
            ->whereDate('waktu_berangkat', '<=', $tanggalJadwal)
            ->whereDate('waktu_kembali', '>=', $tanggalJadwal)
            ->get();

        foreach ($izins as $izin) {
            if ($jadwal->jenis_kegiatan === 'Apel') {
                PresensiApel::where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->where('user_id', $izin->user_id)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);
            } elseif ($jadwal->jenis_kegiatan === 'Senam') {
                PresensiSenam::where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->where('user_id', $izin->user_id)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);
            } elseif ($jadwal->jenis_kegiatan === 'Upacara') {
                PresensiUpacara::where('jadwalKegiatanAsrama_id', $jadwal->id)
                    ->where('user_id', $izin->user_id)
                    ->where('status_kehadiran', 'Alpha')
                    ->update(['status_kehadiran' => 'Izin']);
            }
        }
    }
}
