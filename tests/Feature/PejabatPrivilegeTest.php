<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Database\Seeders\RoleSeeder;
use Database\Seeders\KategoriPelanggaranSeeder;
use Database\Seeders\JenisPelanggaranSeeder;

class PejabatPrivilegeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed necessary data
        $this->seed(RoleSeeder::class);
        $this->seed(KategoriPelanggaranSeeder::class);
        $this->seed(JenisPelanggaranSeeder::class);
    }

    private function createPejabatUser()
    {
        return User::factory()->create([
            'role_id' => User::PEJABAT_ROLE_ID // Assuming PEJABAT_ROLE_ID is defined (usually 9)
        ]);
    }

    public function test_pejabat_can_store_jenis_izin(): void
    {
        $pejabat = $this->createPejabatUser();

        // Simulate POST request to admin.jenis.store
        $response = $this->actingAs($pejabat)->post(route('admin.jenis.store'), [
            'nama' => 'Izin Test',
            'deskripsi' => 'Deskripsi Izin',
            'maksimal_hari' => 3
        ]);

        // It should either succeed (302 redirect back) or fail with validation error, 
        // but it should NOT fail with the Read-Only error.
        $response->assertSessionMissing('error'); // Make sure no 'Akses ditolak' error
        $response->assertStatus(302);
    }

    public function test_pejabat_cannot_delete_jenis_pelanggaran_via_get(): void
    {
        $pejabat = $this->createPejabatUser();

        // Simulate GET request to delete a pelanggaran (ID 1 exists due to seeder)
        $response = $this->actingAs($pejabat)->get(route('admin.deleteJenisPelanggaran', ['id' => 1]));

        // Should return a redirect back with error message
        $response->assertStatus(302);
        $response->assertSessionHas('error', 'Akses ditolak: Role Pejabat hanya memiliki akses Read-Only pada modul ini.');
    }

    public function test_pejabat_cannot_see_admin_privilege_pages_heading(): void
    {
        $pejabat = $this->createPejabatUser();

        $response = $this->actingAs($pejabat)->get('/dashboard-admin'); // Or any page with sidebar

        $response->assertStatus(200);
        $response->assertDontSee('Admin Privilege Pages');
        
        // But should still see "Kelola Jenis Izin" (or its link)
        $response->assertSee('Kelola Jenis Izin');
    }
    public function test_pejabat_can_submit_presense_kamera(): void
    {
        $pejabat = $this->createPejabatUser();
        
        $user = User::factory()->create([
            'role_id' => 3,
            'status' => 'didalam',
        ]);

        $response = $this->actingAs($pejabat)->post(route('admin.presense.api'), [
            'user_id' => $user->id,
            'time' => '10:00:00',
            'date' => now()->toDateString(),
            'status' => 'diluar',
            'scanner' => 'absensi',
        ]);

        $this->assertNotEquals('Akses ditolak: Role Pejabat hanya memiliki akses Read-Only pada modul ini.', session('error'));
        $response->assertStatus(302);
    }

    public function test_pejabat_melihat_data_perizinan_sesuai_lingkup(): void
    {
        $pejabat = $this->createPejabatUser();
        // Beri pejabat lingkup 'prodi' dengan ID 1
        \App\Models\Pejabat::create([
            'user_id' => $pejabat->id,
            'jabatan' => 'kaprodi',
            'lingkup' => 'prodi',
            'lingkup_id' => 1,
            'is_active' => true,
        ]);

        $jenisIzin = \App\Models\JenisIzin::create(['kode' => 'IZN01', 'nama' => 'Izin', 'maksimal_hari' => 1]);

        // Mhs 1: Prodi 1 (seharusnya terlihat)
        $mhsProdi1 = User::factory()->create(['role_id' => 3, 'prodi_id' => 1]);
        \App\Models\PengajuanIzin::create(['user_id' => $mhsProdi1->id, 'jenis_izin_id' => $jenisIzin->id, 'status' => 'berjalan', 'keperluan' => 'KEP1', 'tujuan_lokasi' => 'LOK_PRODI1', 'waktu_berangkat' => now(), 'waktu_kembali' => now(), 'nama_snapshot' => 'Nama 1']);

        // Mhs 2: Prodi 2 (seharusnya TIDAK terlihat)
        $mhsProdi2 = User::factory()->create(['role_id' => 3, 'prodi_id' => 2]);
        \App\Models\PengajuanIzin::create(['user_id' => $mhsProdi2->id, 'jenis_izin_id' => $jenisIzin->id, 'status' => 'berjalan', 'keperluan' => 'KEP2', 'tujuan_lokasi' => 'LOK_PRODI2', 'waktu_berangkat' => now(), 'waktu_kembali' => now(), 'nama_snapshot' => 'Nama 2']);

        // Check Monitor Data API
        $responseMonitor = $this->actingAs($pejabat)->getJson(route('admin.izin.monitor.data'));
        $responseMonitor->assertStatus(200);
        
        $dataMonitor = $responseMonitor->json('data');
        $this->assertCount(1, $dataMonitor);
        $this->assertEquals($mhsProdi1->id, \App\Models\PengajuanIzin::find($dataMonitor[0]['id'])->user_id);

        // Check Data Perizinan Index
        $responseData = $this->actingAs($pejabat)->get(route('admin.izin.data.index'));
        $responseData->assertStatus(200);
        $responseData->assertSee('LOK_PRODI1');
        $responseData->assertDontSee('LOK_PRODI2');
    }
}
