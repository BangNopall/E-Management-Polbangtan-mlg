<?php

namespace Database\Seeders;

use App\Models\Prodi;
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
        Prodi::create([
            'prodi' => 'PPB',
        ]);
        Prodi::create([
            'prodi' => 'PPKH',
        ]);
        Prodi::create([
            'prodi' => 'Agrinak',
        ]);
    }
}
