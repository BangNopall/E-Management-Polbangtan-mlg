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
        // Must match constants in App\Models\User
        // 1: admin, 2: operator, 3: user, 4: pelatih, 5: pembina, 
        // 6: pelatih_ukm, 7: security, 8: dosen_pa, 9: pejabat
        
        $roles = [
            1 => 'admin',       // admin & developer
            2 => 'operator',    // petugas asrama
            3 => 'user',        // mahasiswa
            4 => 'pelatih',     // pelatih
            5 => 'pembina',     // pembina
            6 => 'pelatih_ukm', // pelatih ukm
            7 => 'security',    // security
            8 => 'dosen_pa',    // dosen pa
            9 => 'pejabat',     // pejabat
        ];

        foreach ($roles as $id => $name) {
            Role::updateOrCreate(['id' => $id], ['name' => $name]);
        }
    }
}
