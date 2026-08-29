<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = bcrypt('password');

        // 1. Akun Operator
        User::firstOrCreate(
            ['email' => 'operator@polbangtanmalang.ac.id'],
            [
                'name' => 'Operator Asrama',
                'password' => $password,
                'role_id' => User::OPERATOR_ROLE_ID,
                'no_hp' => '1241251315',
            ]
        );

        // 2. Akun Pelatih
        User::firstOrCreate(
            ['email' => 'pelatih@polbangtanmalang.ac.id'],
            [
                'name' => 'Pelatih Asrama',
                'password' => $password,
                'role_id' => User::PELATIH_ROLE_ID,
                'no_hp' => '081234567890',
            ]
        );

        // 3. Akun Pembina
        User::firstOrCreate(
            ['email' => 'pembina@polbangtanmalang.ac.id'],
            [
                'name' => 'Pembina Asrama',
                'password' => $password,
                'role_id' => User::PEMBINA_ROLE_ID,
                'no_hp' => '1512312312312',
            ]
        );
    }
}
