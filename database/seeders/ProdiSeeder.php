<?php

namespace Database\Seeders;

use App\Models\prodi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        prodi::create([
            'prodi' => 'PPB',
        ]);
        prodi::create([
            'prodi' => 'PPKH',
        ]);
        prodi::create([
            'prodi' => 'Agrinak',
        ]);
    }
}
