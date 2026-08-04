<?php

namespace App\Observers;

use App\Models\Ukm;

class UkmObserver
{
    /**
     * Perbaikan Modul UKM Dinamis — Isu #5.
     * Saat sebuah UKM dinonaktifkan (is_active: true -> false), cascade
     * status seluruh ukm_members yang masih 'aktif' menjadi 'nonaktif'
     * supaya status keanggotaan tetap konsisten dengan status UKM induknya.
     *
     * Sengaja TIDAK ada auto-reaktivasi saat UKM diaktifkan kembali —
     * admin harus mengelola ulang siapa yang masih aktif secara manual
     * (keputusan produk, lihat .claude/prds/perbaikan-modul-ukm-dinamis.prd.md).
     */
    public function updated(Ukm $ukm): void
    {
        if ($ukm->wasChanged('is_active') && ! $ukm->is_active) {
            $ukm->members()->where('status', 'aktif')->update(['status' => 'nonaktif']);
        }
    }
}
