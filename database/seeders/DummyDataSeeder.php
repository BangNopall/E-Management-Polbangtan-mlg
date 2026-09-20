<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\BlokRuangan;
use App\Models\Pelanggaran;
use App\Models\Attendance;
use App\Models\Presence;
use App\Models\Role;
use App\Models\JadwalKegiatanAsrama;
use App\Models\PresensiUpacara;
use App\Models\PresensiApel;
use App\Models\PresensiSenam;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User Development Only
        User::factory()->create([
            'name' => 'Pelatih Kedis',
            'email' => 'pelatihkedis@polbangtanmalang.ac.id',
            'nim' => '1434567891234',
            'blok_ruangan_id' => BlokRuangan::where('name', 'B')->first()?->id ?? 1,
            'kelas_id' => Kelas::first()?->id ?? 1,
            'no_kamar' => '27',
            'prodi_id' => Prodi::first()?->id ?? 1,
            'asal_daerah' => 'Malang',
            'no_hp' => NULL,
            'password' => bcrypt('password'),
            'role_id' => User::PELATIH_ROLE_ID,
        ]);
        
        // factory Development Only
        User::factory(50)->create();
        
        Pelanggaran::factory(20)->create([
            'statusPelanggaran' => 'Submitted'
        ]);        
        Pelanggaran::factory(7)->create([
            'statusPelanggaran' => 'rejected',
            'rejected_message' => 'Input Pelanggaran anda tidak sesuai dengan kriteria yang ada'
        ]);
        
        // Using safe state if accepted_id exists
        $adminId = User::where('role_id', User::ADMIN_ROLE_ID)->first()?->id ?? 1;
        Pelanggaran::factory(100)->state([
            'statusPelanggaran' => 'progressing',
            'Hukuman' => 'Denda Rp. 100.000,-',
            'accepted_id' => $adminId
        ])->create();        
        
        Pelanggaran::factory(100)->state([
            'statusPelanggaran' => 'Done',
            'Hukuman' => 'Denda Rp. 100.000,-',
            'accepted_id' => $adminId
        ])->create(); 
        
        Attendance::factory(50)->create();
        Presence::factory(300)->create();

        JadwalKegiatanAsrama::factory(10)->create();
        PresensiUpacara::factory(50)->create();
        PresensiApel::factory(50)->create();
        PresensiSenam::factory(50)->create();
    }
}
