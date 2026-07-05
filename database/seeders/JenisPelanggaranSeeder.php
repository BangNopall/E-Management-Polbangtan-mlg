<?php

namespace Database\Seeders;

use App\Models\JenisPelanggaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisPelanggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $path = public_path('pelanggaran/dataPelanggaran.sql'); // Ganti 'namafile.sql' dengan nama file SQL Anda
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
    
}
