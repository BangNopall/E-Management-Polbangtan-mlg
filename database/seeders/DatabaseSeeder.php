<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\jadwalKegiatanAsrama;
use App\Models\Kelas;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;
use App\Models\Role;
use App\Models\User;
use App\Models\prodi;
use App\Models\blokRuangan;
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
            'email' => 'admin@asramapolbangtan-mlg.com',
            'password' => bcrypt('password'),
            'role_id' => 1,
        ]);

        
        User::create([
            'name' => 'Developer Asrama Polbangtan',
            'email' => 'developer@asramapolbangtan-mlg.com',
            'password' => bcrypt('@asramaPolbangtan2023'),
            'role_id' => 1,
        ]);

        User::create([
            'name' => 'user Asrama Polbangtan',
            'email' => 'user@gmail.com',
            'password' => bcrypt('password'),
            'role_id' => 3,
        ]);
        // User Development Only
        // User::factory()->create([
        //     'name' => 'User Development Asrama Polbangtan',
        //     'email' => 'user@asramapolbangtan-mlg.com',
        //     'nim' => '1234567891234',
        //     'blok_ruangan_id' => blokRuangan::where('name', 'B')->first('id'),
        //     'kelas_id' => Kelas::where('nama_kelas', 'Agrinak 1-B')->first('id'),
        //     'no_kamar' => '27',
        //     'prodi_id' => prodi::where('prodi', 'Agrinak')->first('id'),
        //     'asal_daerah' => 'Malang',
        //     'no_hp' => '081233219133',
        //     'password' => bcrypt('password'),
        //     'role_id' => Role::where('name', 'user')->first('id'),
        // ]);
        // User::factory()->create([
        //     'name' => 'Operator Development Asrama Polbangtan',
        //     'email' => 'operator@asramapolbangtan-mlg.com',
        //     'nim' => '1234566891234',
        //     'blok_ruangan_id' => blokRuangan::where('name', 'B')->first('id'),
        //     'kelas_id' => Kelas::where('nama_kelas', 'PPB 1-B')->first('id'),
        //     'no_kamar' => '28',
        //     'prodi_id' => prodi::where('prodi', 'PPKH')->first('id'),
        //     'asal_daerah' => 'Malang',
        //     'no_hp' => '081234219133',
        //     'password' => bcrypt('password'),
        //     'role_id' => Role::where('name', 'operator')->first('id'),
        // ]);
        // User::factory()->create([
        //     'name' => 'Pelatih Development Asrama Polbangtan',
        //     'email' => 'pelatih@asramapolbangtan-mlg.com',
        //     'nim' => '1434567891234',
        //     'blok_ruangan_id' => blokRuangan::where('name', 'B')->first('id'),
        //     'kelas_id' => Kelas::where('nama_kelas', 'PPB 1-B')->first('id'),
        //     'no_kamar' => '27',
        //     'prodi_id' => prodi::where('prodi', 'Agrinak')->first('id'),
        //     'asal_daerah' => 'Malang',
        //     'no_hp' => '082233219133',
        //     'password' => bcrypt('password'),
        //     'role_id' => Role::where('name', 'pelatih')->first('id'),
        // ]);
        
        // factory Development Only
        User::factory(50)->create();
        Pelanggaran::factory(20)->create([
            'statusPelanggaran' => 'Submitted'
        ]);        
        Pelanggaran::factory(7)->create([
            'statusPelanggaran' => 'rejected',
            'rejected_message' => 'Input Pelanggaran anda tidak sesuai dengan kriteria yang ada'
        ]);
        Pelanggaran::factory(500)->state([
            'statusPelanggaran' => 'progressing',
            'Hukuman' => 'Denda Rp. 100.000,-',
            'accepted_id' => User::where('role_id', Role::where('name', 'admin')->first()->id)->first()->id
        ])->create();        
        Pelanggaran::factory(500)->state([
            'statusPelanggaran' => 'Done',
            'Hukuman' => 'Denda Rp. 100.000,-',
            'accepted_id' => User::where('role_id', Role::where('name', 'admin')->first()->id)->first()->id
        ])->create(); 
        Attendance::factory(200)->create();
        Presence::factory(1500)->create();

        jadwalKegiatanAsrama::factory(21)->create();
        PresensiUpacara::factory(1000)->create();
        PresensiApel::factory(1000)->create();
        PresensiSenam::factory(1000)->create();
    }
}
