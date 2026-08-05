<?php

namespace Tests\Feature\Ukm;

use App\Models\Role;
use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\UkmPresensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UkmBatch3Test extends TestCase
{
    use RefreshDatabase;

    private function makeUser(int $roleId): User
    {
        return User::factory()->create([
            'role_id' => $roleId,
            'blok_ruangan_id' => null,
            'kelas_id' => null,
            'prodi_id' => null,
        ]);
    }

    public function test_mahasiswa_bisa_mengakses_halaman_riwayat_absen_ukm(): void
    {
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Robotik', 'slug' => 'ukm-robotik', 'is_active' => true]);
        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mahasiswa->id, 'peran' => 'anggota', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin Robotik',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '15:00',
            'selesai_acara' => '17:00',
            'lokasi' => 'Lab Komputer',
            'status_verifikasi' => 'disetujui',
            'created_by' => $mahasiswa->id,
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir',
            'jam_scan' => now(),
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('home.ukm.riwayatAbsen'));

        $response->assertStatus(200);
        $response->assertViewIs('ukm.riwayat-ukm');
        $response->assertSee('Latihan Rutin Robotik');
        $response->assertSee('UKM Robotik');
        $response->assertSee('Hadir');
    }

    public function test_pengelola_bisa_melihat_rekap_presensi_anggota_pada_detail_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Catur', 'slug' => 'ukm-catur', 'is_active' => true]);

        UkmMember::create(['ukm_id' => $ukm->id, 'user_id' => $mahasiswa->id, 'peran' => 'anggota', 'status' => 'aktif']);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Turnamen Catur Internal',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '08:00',
            'selesai_acara' => '12:00',
            'lokasi' => 'Aula Utama',
            'status_verifikasi' => 'disetujui',
            'created_by' => $admin->id,
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir',
            'jam_scan' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ukm.show', $ukm->id));

        $response->assertStatus(200);
        $response->assertSee('Rekap Presensi');
        $response->assertSee('Turnamen Catur Internal');
        $response->assertSee($mahasiswa->name);
        $response->assertSee('Hadir');
    }

    public function test_detail_absen_kegiatan_mahasiswa_menampilkan_riwayat_ukm(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Seni Musik', 'slug' => 'ukm-seni-musik', 'is_active' => true]);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Konser Musik Asrama',
            'jenis' => 'latihan',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '19:00',
            'selesai_acara' => '21:00',
            'lokasi' => 'Panggung Utama',
            'status_verifikasi' => 'disetujui',
            'created_by' => $admin->id,
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir',
            'jam_scan' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dataKegiatanWajibDetail', $mahasiswa->id));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Presensi UKM');
        $response->assertSee('UKM Seni Musik');
        $response->assertSee('Konser Musik Asrama');
        $response->assertSee('Hadir');
    }

    public function test_admin_bisa_generate_laporan_ukm_pdf_dan_excel(): void
    {
        $admin = $this->makeUser(User::ADMIN_ROLE_ID);
        $mahasiswa = $this->makeUser(User::USER_ROLE_ID);
        $ukm = Ukm::create(['nama' => 'UKM Taekwondo', 'slug' => 'ukm-taekwondo', 'is_active' => true]);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Ujian Kenaikan Sabuk',
            'jenis' => 'kegiatan_wajib',
            'tanggal' => now()->toDateString(),
            'mulai_acara' => '08:00',
            'selesai_acara' => '12:00',
            'lokasi' => 'GOR Polbangtan',
            'status_verifikasi' => 'disetujui',
            'created_by' => $admin->id,
        ]);

        UkmPresensi::create([
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir',
            'jam_scan' => now(),
        ]);

        // 1. Test PDF Export
        $responsePdf = $this->actingAs($admin)->post(route('admin.generateLaporanUkm'), [
            'ukm_id' => $ukm->id,
            'submit' => 'pdf',
        ]);

        $responsePdf->assertStatus(302); // Redirect to generated PDF URL

        // 2. Test Excel Export
        $responseExcel = $this->actingAs($admin)->post(route('admin.generateLaporanUkm'), [
            'ukm_id' => $ukm->id,
            'submit' => 'excel',
        ]);

        $responseExcel->assertStatus(302); // Redirect to generated XLSX URL
    }
}
