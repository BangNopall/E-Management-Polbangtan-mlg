<?php

namespace Database\Seeders;

use App\Models\JenisIzin;
use Illuminate\Database\Seeder;

class JenisIzinSeeder extends Seeder
{
    /**
     * Run the database seeds for permission types and workflow steps.
     */
    public function run(): void
    {
        // 1. Form A: IJIN KELUAR ASRAMA (AR.009)
        $izinKeluar = JenisIzin::firstOrCreate(
            ['kode' => 'IZIN_KELUAR'],
            [
                'nama' => 'Ijin Keluar Asrama',
                'deskripsi' => 'Izin keluar area asrama untuk keperluan kegiatan organisasi, perihal dinas, atau pribadi.',
                'kode_form' => 'AR.009',
                'butuh_bermalam' => false,
                'butuh_ukm' => true,
                'butuh_konfirmasi_tiba' => true,
                'maks_durasi_jam' => 24,
                'min_ajukan_jam' => 12,
                'is_active' => true,
            ]
        );

        // Workflow Steps for Form A
        $izinKeluarSteps = [
            [
                'urutan' => 1,
                'label' => 'Menyetujui, Pembina ORMAWA/UKM',
                'blok' => 'Persetujuan Kegiatan & Akademik',
                'resolver' => 'pembina_ukm',
                'jabatan' => null,
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'jika_ada_ukm',
                'resolve_saat' => 'submit',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 24,
                'is_active' => true,
            ],
            [
                'urutan' => 2,
                'label' => 'Mengetahui, Dosen PA',
                'blok' => 'Persetujuan Kegiatan & Akademik',
                'resolver' => 'dosen_pa',
                'jabatan' => null,
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'submit',
                'fallback_resolver' => 'pejabat',
                'fallback_jabatan' => ['kaprodi'],
                'sla_jam' => 24,
                'is_active' => true,
            ],
            [
                'urutan' => 3,
                'label' => 'Mengetahui, Petugas Piket Asrama',
                'blok' => 'Validasi Asrama',
                'resolver' => 'petugas_jaga',
                'jabatan' => null,
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'langkah_aktif',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 12,
                'is_active' => true,
            ],
            [
                'urutan' => 4,
                'label' => 'Memvalidasi, Kepala Asrama/Unit Kemahasiswaan',
                'blok' => 'Validasi Asrama',
                'resolver' => 'pejabat',
                'jabatan' => ['kepala_asrama', 'unit_kemahasiswaan'],
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'submit',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($izinKeluarSteps as $stepData) {
            $izinKeluar->steps()->updateOrCreate(
                ['urutan' => $stepData['urutan']],
                $stepData
            );
        }

        // 2. Form B: IJIN BERMALAM / MENINGGALKAN KELAS (IB)
        $ib = JenisIzin::firstOrCreate(
            ['kode' => 'IB'],
            [
                'nama' => 'Ijin Bermalam / Meninggalkan Kelas',
                'deskripsi' => 'Izin bermalam di luar asrama atau meninggalkan kegiatan akademik/kelas.',
                'kode_form' => 'IB',
                'butuh_bermalam' => true,
                'butuh_ukm' => false,
                'butuh_konfirmasi_tiba' => true,
                'maks_durasi_jam' => 72,
                'min_ajukan_jam' => 24,
                'is_active' => true,
            ]
        );

        // Workflow Steps for Form B
        $ibSteps = [
            [
                'urutan' => 1,
                'label' => 'Menyetujui, Ketua Program Studi',
                'blok' => 'Persetujuan Kegiatan & Akademik',
                'resolver' => 'pejabat',
                'jabatan' => ['kaprodi'],
                'lingkup' => 'prodi',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'submit',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 24,
                'is_active' => true,
            ],
            [
                'urutan' => 2,
                'label' => 'Mengetahui, Dosen Pembimbing Akademik',
                'blok' => 'Persetujuan Kegiatan & Akademik',
                'resolver' => 'dosen_pa',
                'jabatan' => null,
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'submit',
                'fallback_resolver' => 'pejabat',
                'fallback_jabatan' => ['kaprodi'],
                'sla_jam' => 24,
                'is_active' => true,
            ],
            [
                'urutan' => 3,
                'label' => 'Mengetahui, Pelatih Harian Asrama',
                'blok' => 'Validasi Asrama',
                'resolver' => 'petugas_jaga',
                'jabatan' => null,
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'langkah_aktif',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 12,
                'is_active' => true,
            ],
            [
                'urutan' => 4,
                'label' => 'Memvalidasi, Kepala Asrama/Unit Kemahasiswaan',
                'blok' => 'Validasi Asrama',
                'resolver' => 'pejabat',
                'jabatan' => ['kepala_asrama', 'unit_kemahasiswaan'],
                'lingkup' => 'global',
                'mode' => 'any',
                'kondisi' => 'selalu',
                'resolve_saat' => 'submit',
                'fallback_resolver' => null,
                'fallback_jabatan' => null,
                'sla_jam' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($ibSteps as $stepData) {
            $ib->steps()->updateOrCreate(
                ['urutan' => $stepData['urutan']],
                $stepData
            );
        }
    }
}
