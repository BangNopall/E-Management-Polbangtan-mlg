<?php

namespace Database\Seeders;

use App\Models\blokRuangan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlokRuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        blokRuangan::create([
            'name' => 'A',
        ]);
        blokRuangan::create([
            'name' => 'B',
        ]);
        blokRuangan::create([
            'name' => 'C',
        ]);
        blokRuangan::create([
            'name' => 'D',
        ]);
        blokRuangan::create([
            'name' => 'E',
        ]);
    }
}
