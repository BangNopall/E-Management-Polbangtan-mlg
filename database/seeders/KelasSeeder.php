<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\prodi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seeder untuk Level Kelas
        LevelKelas::create(['nama_level_kelas' => 'A']);
        LevelKelas::create(['nama_level_kelas' => 'B']);
        LevelKelas::create(['nama_level_kelas' => 'C']);

        // Seeder untuk Kelas
        foreach (prodi::all() as $prodis) {
            foreach (LevelKelas::all() as $levelKelas) {
                for ($i = 1; $i <= 4; $i++) {
                    Kelas::create([
                        'kelas' =>  $i,
                        'nama_kelas' => $prodis->prodi . ' ' . $i . '-' . $levelKelas->nama_level_kelas,
                        'prodi_id' => $prodis->id,
                        'level_kelas_id' => $levelKelas->id,
                    ]);
                }
            }
        }
    }
}
