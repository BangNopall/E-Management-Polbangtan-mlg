<?php

namespace Database\Seeders;

use App\Models\Pejabat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PejabatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = bcrypt('password');

        // 1. Kaprodi
        $kaprodiUser = User::firstOrCreate(
            ['email' => 'kaprodi@polbangtanmalang.ac.id'],
            [
                'name' => 'Bapak Kaprodi',
                'password' => $password,
                'role_id' => User::PEJABAT_ROLE_ID,
                'no_hp' => '081111111111',
            ]
        );

        Pejabat::firstOrCreate(
            [
                'jabatan' => 'kaprodi',
                'lingkup' => 'global',
            ],
            [
                'user_id' => $kaprodiUser->id,
                'mulai_menjabat' => now(),
                'is_active' => true,
            ]
        );

        // 2. Kepala Asrama
        $kepalaAsramaUser = User::firstOrCreate(
            ['email' => 'kepala_asrama@polbangtanmalang.ac.id'],
            [
                'name' => 'Bapak Kepala Asrama',
                'password' => $password,
                'role_id' => User::PEJABAT_ROLE_ID,
                'no_hp' => '082222222222',
            ]
        );

        Pejabat::firstOrCreate(
            [
                'jabatan' => 'kepala_asrama',
                'lingkup' => 'global',
            ],
            [
                'user_id' => $kepalaAsramaUser->id,
                'mulai_menjabat' => now(),
                'is_active' => true,
            ]
        );

        // 3. Unit Kemahasiswaan
        $unitKemaUser = User::firstOrCreate(
            ['email' => 'unit_kemahasiswaan@polbangtanmalang.ac.id'],
            [
                'name' => 'Unit Kemahasiswaan',
                'password' => $password,
                'role_id' => User::PEJABAT_ROLE_ID,
                'no_hp' => '083333333333',
            ]
        );

        Pejabat::firstOrCreate(
            [
                'jabatan' => 'unit_kemahasiswaan',
                'lingkup' => 'global',
            ],
            [
                'user_id' => $unitKemaUser->id,
                'mulai_menjabat' => now(),
                'is_active' => true,
            ]
        );
    }
}
