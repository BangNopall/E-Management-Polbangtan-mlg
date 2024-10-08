<?php

namespace Database\Seeders;

use App\Models\KategoriPelanggaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 8.Kategori dan Jenis Pelanggaran
        KategoriPelanggaran::create([
            'name' => 'LINGKUNGAN ASRAMA',
        ]);
        KategoriPelanggaran::create([
            'name' => 'DI LINGKUNGAN KANTIN / RUANG MAKAN',
        ]);
        KategoriPelanggaran::create([
            'name' => 'KELUAR DAN MASUK KAMPUS',
        ]);
        KategoriPelanggaran::create([
            'name' => 'PENAMPILAN',
        ]);
        KategoriPelanggaran::create([
            'name' => 'TINDAKAN MERUSAK / MENGOTORI LINGKUNGAN / FASILITAS KAMPUS',
        ]);
        KategoriPelanggaran::create([
            'name' => 'MEROKOK / NARKOBA / PERJUDIAN / MIRAS',
        ]);
        KategoriPelanggaran::create([
            'name' => 'MELAKUKAN TINDAKAN TIDAK TERPUJI LAINNYA',
        ]);
    }
}
