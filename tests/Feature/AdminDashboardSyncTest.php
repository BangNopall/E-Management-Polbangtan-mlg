<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\BlokRuangan;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\PengajuanIzin;
use App\Models\Presence;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BlokRuangan $blok;
    protected Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => User::ADMIN_ROLE_ID,
            'name' => 'Admin Utama',
        ]);

        $this->blok = BlokRuangan::create(['name' => 'Blok Melati']);
        $prodi = Prodi::create(['prodi' => 'Agroindustri']);
        $level = LevelKelas::create(['nama_level_kelas' => 'Tingkat 2']);
        $this->kelas = Kelas::create([
            'kelas' => 'AG-2B',
            'nama_kelas' => 'AG-2B',
            'prodi_id' => $prodi->id,
            'level_kelas_id' => $level->id,
        ]);
    }

    public function test_dashboard_menghitung_jumlah_ruangan_dan_seluruh_petugas_dengan_benar(): void
    {
        $initialBlokCount = BlokRuangan::count();

        // Buat 3 blok ruangan tambahan
        BlokRuangan::create(['name' => 'Blok Mawar']);
        BlokRuangan::create(['name' => 'Blok Anggrek']);
        BlokRuangan::create(['name' => 'Blok Tulip']);

        $expectedBlokCount = $initialBlokCount + 3;

        // Buat berbagai petugas dengan role berbeda
        User::factory()->create(['role_id' => User::OPERATOR_ROLE_ID, 'name' => 'Petugas Operator']);
        User::factory()->create(['role_id' => User::PELATIH_ROLE_ID, 'name' => 'Pelatih Kedisiplinan']);
        User::factory()->create(['role_id' => User::PEMBINA_ROLE_ID, 'name' => 'Pembina UKM']);
        User::factory()->create(['role_id' => User::SECURITY_ROLE_ID, 'name' => 'Petugas Security']);

        // Buat 5 mahasiswa
        User::factory()->count(5)->create(['role_id' => User::USER_ROLE_ID]);

        $response = $this->actingAs($this->admin)->get(route('admin.index'));
        $response->assertStatus(200);

        // Jumlah Ruangan harus sesuai dengan total BlokRuangan
        $response->assertViewHas('ruanganTerisi', $expectedBlokCount);

        // Jumlah Petugas harus mencakup semua staff (1 admin + 1 operator + 1 pelatih + 1 pembina + 1 security)
        $totalStaff = User::whereIn('role_id', [
            User::ADMIN_ROLE_ID,
            User::OPERATOR_ROLE_ID,
            User::PELATIH_ROLE_ID,
            User::PEMBINA_ROLE_ID,
            User::PELATIH_UKM_ROLE_ID,
            User::SECURITY_ROLE_ID,
            User::DOSEN_PA_ROLE_ID,
            User::PEJABAT_ROLE_ID,
        ])->count();
        $response->assertViewHas('jumlahPetugas', $totalStaff);

        // Jumlah Mahasiswa harus sesuai
        $response->assertViewHas('userCount', User::where('role_id', User::USER_ROLE_ID)->count());
    }

    public function test_dashboard_memuat_ringkasan_perizinan_dan_status_kehadiran(): void
    {
        $student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'kelas_id' => $this->kelas->id,
            'status' => 'izin',
        ]);

        $jenisIzin = JenisIzin::create([
            'nama' => 'Izin Dinas',
            'kode' => 'IDN',
            'is_active' => true,
        ]);

        PengajuanIzin::create([
            'user_id' => $student->id,
            'jenis_izin_id' => $jenisIzin->id,
            'keperluan' => 'Lomba Karya Ilmiah',
            'tujuan_lokasi' => 'Jakarta',
            'waktu_berangkat' => now()->subDay(),
            'waktu_kembali' => now()->addDays(2),
            'nama_snapshot' => $student->name,
            'status' => 'berjalan',
        ]);

        // Buat presensi 7 hari terakhir untuk cek bug $record vs $d
        $attendance = Attendance::create([
            'title' => 'Absensi Harian',
            'date' => now()->toDateString(),
            'start_time' => '06:00:00',
            'end_time' => '22:00:00',
        ]);

        Presence::create([
            'user_id' => $student->id,
            'attendance_id' => $attendance->id,
            'presence_date' => now()->toDateString(),
            'presence_keluar' => '08:00:00',
            'log_status' => 'izin',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.index'));
        $response->assertStatus(200);

        // Pastikan nama kelas muncul pada riwayat 7 hari (memastikan bug $record teratasi)
        $response->assertSee('AG-2B');
        $response->assertViewHas('izinBerjalanCount', 1);
        $response->assertViewHas('userStatusIzin', 1);
    }
}
