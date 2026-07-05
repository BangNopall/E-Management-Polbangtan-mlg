<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'admin', // admin & developer (1)
        ]);
        Role::create([
            'name' => 'operator', // petugas (2)
        ]);
        Role::create([
            'name' => 'user', // mahasiswa (3)
        ]);
        Role::create([
            'name' => 'pelatih',  // pelatih (4)
        ]);
    }
}
