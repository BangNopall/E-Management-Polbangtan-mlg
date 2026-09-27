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
        BlokRuangan::firstOrCreate(['name' => 'A']);
        BlokRuangan::firstOrCreate(['name' => 'B']);
        BlokRuangan::firstOrCreate(['name' => 'C']);
        BlokRuangan::firstOrCreate(['name' => 'D']);
        BlokRuangan::firstOrCreate(['name' => 'E']);
    }
}
