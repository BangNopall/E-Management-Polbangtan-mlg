<?php

namespace Database\Seeders;

use App\Models\BlokRuangan;
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
        BlokRuangan::create([
            'name' => 'A',
        ]);
        BlokRuangan::create([
            'name' => 'B',
        ]);
        BlokRuangan::create([
            'name' => 'C',
        ]);
        BlokRuangan::create([
            'name' => 'D',
        ]);
        BlokRuangan::create([
            'name' => 'E',
        ]);
    }
}
