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

    public function test_dosen_pa_lain_tidak_bisa_melihat_detail_izin_mahasiswa_bukan_bimbingannya(): void
    {
        $dosenPa1 = User::factory()->create(['role_id' => User::DOSEN_PA_ROLE_ID]);
        $dosenPa2 = User::factory()->create(['role_id' => User::DOSEN_PA_ROLE_ID]);

        $student1 = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'dosen_pa_id' => $dosenPa1->id,
        ]);

        $jenisIzin = \App\Models\JenisIzin::first() ?? \App\Models\JenisIzin::create([
            'nama' => 'Izin Test',
            'kode' => 'IT',
            'is_active' => true,
        ]);

        $pengajuan = \App\Models\PengajuanIzin::create([
            'user_id' => $student1->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Keperluan Test',
            'tujuan_lokasi' => 'Malang',
            'waktu_berangkat' => now()->addDay(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $student1->name,
            'status' => 'diajukan',
            'qr_token' => 'token_' . uniqid(),
        ]);

        // Dosen PA 2 attempts to view Student 1's leave details
        $response = $this->actingAs($dosenPa2)->get("/izin/data/{$pengajuan->id}");

        $response->assertStatus(403);
    }

    public function test_mahasiswa_tidak_bisa_melihat_detail_pelanggaran_mahasiswa_lain(): void
    {
        $studentA = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'no_hp' => '08' . rand(1000000000, 9999999999),
            'kelas_id' => 1,
            'blok_ruangan_id' => 1,
            'no_kamar' => '101',
            'asal_daerah' => 'Malang',
            'email' => 'studentA_' . uniqid() . '@example.com',
            'password' => Hash::make('ValidPassword123'),
            'is_password_changed' => true,
        ]);

        $studentB = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'no_hp' => '08' . rand(1000000000, 9999999999),
            'kelas_id' => 1,
            'blok_ruangan_id' => 1,
            'no_kamar' => '102',
            'asal_daerah' => 'Malang',
            'email' => 'studentB_' . uniqid() . '@example.com',
            'password' => Hash::make('ValidPassword123'),
            'is_password_changed' => true,
        ]);

        $kategori = \App\Models\KategoriPelanggaran::first() ?? \App\Models\KategoriPelanggaran::create([
            'nama_kategori' => 'Disiplin',
        ]);

        $pelanggaran = \App\Models\Pelanggaran::create([
            'user_id' => $studentB->id,
            'kategori_pelanggaran_id' => $kategori->id,
            'statusPelanggaran' => 'done',
        ]);

        // Student A tries to view Student B's violation detail
        $response = $this->actingAs($studentA)->get(route('home.riwayatPelanggaranDetail', $pelanggaran->id));

        $this->assertTrue(in_array($response->getStatusCode(), [403, 404]), 'Student must not view peer violation details');
    }

    public function test_staf_wajib_ganti_password_default(): void
    {
        $operator = User::factory()->create([
            'role_id' => User::OPERATOR_ROLE_ID,
            'password' => Hash::make('password'),
            'is_password_changed' => false,
        ]);

        $response = $this->actingAs($operator)->get('/piket-petugas');

        // Must be redirected to profile edit to change default password
        $response->assertRedirect(route('admin.profil', $operator->id));
        $response->assertSessionHas('error');
    }
}
