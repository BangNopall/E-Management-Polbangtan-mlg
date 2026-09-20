<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileIdorProtectionTest extends TestCase
{
    public function test_mahasiswa_tidak_bisa_mengganti_password_user_lain_via_profil_gmail(): void
    {
        $studentA = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $originalAdminPassword = $admin->password;

        // Make sure studentA's profile is complete so EnsureProfileCompleted middleware allows the request
        $studentA->update([
            'no_hp' => '081' . rand(100000000, 999999999),
            'kelas_id' => 1,
            'blok_ruangan_id' => 1,
            'no_kamar' => '101',
            'asal_daerah' => 'Malang',
            'email' => 'studentA_' . uniqid() . '@example.com',
            'password' => Hash::make('newpassword123'),
        ]);

        // Student A attempts IDOR attack on Admin's account
        $response = $this->actingAs($studentA)->post("/dashboard/profil-gmail/{$admin->id}", [
            'no_hp' => '089999999999',
            'email' => 'hacked_admin@example.com',
            'password' => 'HackedPassword123',
        ]);

        $response->assertStatus(403);

        $admin->refresh();
        $this->assertEquals($originalAdminPassword, $admin->password, 'Admin password must NOT be changed by student IDOR attack');
    }

    public function test_mahasiswa_tidak_bisa_mengubah_profil_user_lain(): void
    {
        $students = User::where('role_id', User::USER_ROLE_ID)->take(2)->get();
        if ($students->count() < 2) {
            $this->markTestSkipped('Need at least 2 student accounts for IDOR test');
        }

        $studentA = $students[0];
        $studentB = $students[1];
        $originalNameB = $studentB->name;

        $studentA->update([
            'no_hp' => '081' . rand(100000000, 999999999),
            'kelas_id' => 1,
            'blok_ruangan_id' => 1,
            'no_kamar' => '101',
            'asal_daerah' => 'Malang',
            'email' => 'studentA2_' . uniqid() . '@example.com',
            'password' => Hash::make('newpassword123'),
        ]);

        $response = $this->actingAs($studentA)->post("/dashboard/profil/{$studentB->id}", [
            'name' => 'Name Changed By Attacker',
            'kelas_id' => $studentB->kelas_id ?? 1,
            'blok_ruangan_id' => $studentB->blok_ruangan_id ?? 1,
            'no_kamar' => 102,
            'asal_daerah' => 'Surabaya',
        ]);

        $response->assertStatus(403);

        $studentB->refresh();
        $this->assertEquals($originalNameB, $studentB->name, 'Student B name must NOT be changed by student A');
    }

    public function test_mahasiswa_tidak_bisa_menghapus_foto_user_lain(): void
    {
        $students = User::where('role_id', User::USER_ROLE_ID)->take(2)->get();
        if ($students->count() < 2) {
            $this->markTestSkipped('Need at least 2 student accounts for IDOR test');
        }

        $studentA = $students[0];
        $studentB = $students[1];

        $studentA->update([
            'no_hp' => '081' . rand(100000000, 999999999),
            'kelas_id' => 1,
            'blok_ruangan_id' => 1,
            'no_kamar' => '101',
            'asal_daerah' => 'Malang',
            'email' => 'studentA3_' . uniqid() . '@example.com',
            'password' => Hash::make('newpassword123'),
        ]);

        $response = $this->actingAs($studentA)->post("/dashboard/delete-foto/{$studentB->id}");

        $response->assertStatus(403);
    }
}
