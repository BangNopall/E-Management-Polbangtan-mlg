<?php

namespace Tests\Feature;

use App\Models\JadwalPetugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PiketPetugasDanKameraScanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $operator;
    protected User $security;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => User::ADMIN_ROLE_ID,
            'name' => 'Admin User',
        ]);

        $this->operator = User::factory()->create([
            'role_id' => User::OPERATOR_ROLE_ID,
            'name' => 'Operator Duty',
        ]);

        $this->security = User::factory()->create([
            'role_id' => User::SECURITY_ROLE_ID,
            'name' => 'Security Guard',
        ]);
    }

    public function test_daftar_petugas_piket_hanya_memuat_role_operator(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.piketPetugas'));
        $response->assertStatus(200);

        $usersInView = $response->viewData('users');
        $this->assertTrue($usersInView->contains('id', $this->operator->id));
        $this->assertFalse($usersInView->contains('id', $this->security->id));
    }

    public function test_kamera_scan_ditolak_jika_jadwal_piket_belum_dibuat(): void
    {
        // Pastikan tidak ada JadwalPetugas untuk hari ini
        JadwalPetugas::where('date', Carbon::today()->toDateString())->delete();

        $response = $this->actingAs($this->admin)->get(route('admin.kamera'));
        $response->assertRedirect(route('admin.index'));
        $response->assertSessionHas('error');
    }

    public function test_kamera_scan_ditolak_jika_user_petugas_masih_kosong(): void
    {
        // Jadwal ada, tetapi petugas1_id dan petugas2_id null (misal hasil auto-generate)
        JadwalPetugas::create([
            'date' => Carbon::today()->toDateString(),
            'petugas1_id' => null,
            'petugas2_id' => null,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kamera'));
        $response->assertRedirect(route('admin.index'));
        $response->assertSessionHas('error', 'Petugas piket hari ini belum ditentukan. Silakan tetapkan petugas piket terlebih dahulu.');
    }

    public function test_kamera_scan_bisa_dibuka_jika_jadwal_dan_petugas_lengkap(): void
    {
        $operator2 = User::factory()->create(['role_id' => User::OPERATOR_ROLE_ID]);

        JadwalPetugas::create([
            'date' => Carbon::today()->toDateString(),
            'petugas1_id' => $this->operator->id,
            'petugas2_id' => $operator2->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.kamera'));
        $response->assertStatus(200);
        $response->assertViewIs('admin.kamera');
    }
}
