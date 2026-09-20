<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\JadwalKegiatanAsrama;
use App\Models\Kelas;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\Role;
use App\Models\User;
use App\Models\Prodi;
use App\Models\BlokRuangan;
use App\Models\Pelanggaran;
use App\Models\Attendance;
use App\Models\Presence;
use App\Models\PresensiUpacara;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\KelasSeeder;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\BlokRuanganSeeder;
use Database\Seeders\KategoriPelanggaranSeeder;
use Database\Seeders\JenisPelanggaranSeeder;
use Database\Seeders\StaffSeeder;
use Database\Seeders\PejabatSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);


        $this->call(BlokRuanganSeeder::class);
        $this->call(ProdiSeeder::class);
        $this->call(KelasSeeder::class);
        $this->call(KategoriPelanggaranSeeder::class);
        $this->call(JenisPelanggaranSeeder::class);

        User::create([
            'name' => 'Admin Asrama Polbangtan',
            'email' => 'admin@polbangtanmalang.ac.id',
            'password' => bcrypt('password'),
            'role_id' => User::ADMIN_ROLE_ID,
        ]);

        User::create([
            'name' => 'Developer Asrama Polbangtan',
            'email' => 'developer@polbangtanmalang.ac.id',
            'password' => bcrypt('password'),
            'role_id' => User::ADMIN_ROLE_ID,
        ]);

        User::create([
            'name' => 'user Asrama Polbangtan',
            'email' => 'user@gmail.com',
            'nim' => '245150307111006',
            'password' => bcrypt('password'),
            'role_id' => User::USER_ROLE_ID,
            'prodi_id' => 1,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DummyDataSeeder::class);
        }

        $this->call(PejabatSeeder::class);
        $this->call(StaffSeeder::class);
        $this->call(UkmSeeder::class);
        $this->call(JenisIzinSeeder::class);
    }
}
