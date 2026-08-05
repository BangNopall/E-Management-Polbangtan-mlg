<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class UkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotent seeding using updateOrCreate.
     */
    public function run(): void
    {
        // 1. Ambil user sampel untuk pelatih, pembina, dan anggota
        $pelatih = User::where('role_id', User::PELATIH_ROLE_ID)->first()
            ?? User::where('role_id', User::ADMIN_ROLE_ID)->first();

        $pembina = User::where('role_id', User::PEMBINA_ROLE_ID)->first()
            ?? User::where('role_id', User::ADMIN_ROLE_ID)->first();

        $students = User::where('role_id', User::USER_ROLE_ID)->take(5)->get();

        // 2. Seed UKM Voli
        $ukmVoli = Ukm::updateOrCreate(
            ['slug' => 'ukm-voli'],
            [
                'nama' => 'UKM Voli',
                'deskripsi' => 'Unit Kegiatan Mahasiswa Olahraga Bola Voli Polbangtan Malang',
                'is_active' => true,
            ]
        );

        // 3. Seed UKM Pramuka
        $ukmPramuka = Ukm::updateOrCreate(
            ['slug' => 'ukm-pramuka'],
            [
                'nama' => 'UKM Pramuka',
                'deskripsi' => 'Gerakan Pramuka Gugus Depan Polbangtan Malang',
                'is_active' => true,
            ]
        );

        // 4. Seed Anggota & Pembina UKM Voli
        if ($pembina) {
            UkmMember::updateOrCreate(
                ['ukm_id' => $ukmVoli->id, 'user_id' => $pembina->id],
                ['peran' => 'pembina', 'status' => 'aktif']
            );
        }

        if ($pelatih) {
            UkmMember::updateOrCreate(
                ['ukm_id' => $ukmVoli->id, 'user_id' => $pelatih->id],
                ['peran' => 'pelatih', 'status' => 'aktif']
            );
        }

        foreach ($students as $student) {
            UkmMember::updateOrCreate(
                ['ukm_id' => $ukmVoli->id, 'user_id' => $student->id],
                ['peran' => 'anggota', 'status' => 'aktif']
            );
        }

        // 5. Seed Anggota UKM Pramuka
        if ($pembina) {
            UkmMember::updateOrCreate(
                ['ukm_id' => $ukmPramuka->id, 'user_id' => $pembina->id],
                ['peran' => 'pembina', 'status' => 'aktif']
            );
        }

        foreach ($students->take(3) as $student) {
            UkmMember::updateOrCreate(
                ['ukm_id' => $ukmPramuka->id, 'user_id' => $student->id],
                ['peran' => 'anggota', 'status' => 'aktif']
            );
        }
    }
}
