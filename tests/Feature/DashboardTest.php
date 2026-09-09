<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PengajuanIzin;
use App\Models\JenisIzin;
use App\Models\Presence;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_melihat_status_izin_dan_rekapan(): void
    {
        $mahasiswa = User::factory()->create([
            'role_id' => 3,
            'status' => 'izin',
            'password' => bcrypt('password123'),
            'asal_daerah' => 'Valid',
            'no_kamar' => '101'
        ]);

        $jenis = JenisIzin::create(['kode' => 'IZN1', 'nama' => 'Izin Pulang', 'maksimal_hari' => 1]);

        $izinAktif = PengajuanIzin::create([
            'user_id' => $mahasiswa->id,
            'jenis_izin_id' => $jenis->id,
            'status' => 'berjalan',
            'keperluan' => 'Pulang',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => Carbon::now()->subDay(),
            'waktu_kembali' => Carbon::now()->addDay(),
            'nama_snapshot' => $mahasiswa->name,
        ]);

        $attendance = Attendance::create([
            'title' => 'Absen',
            'date' => Carbon::now()->format('Y-m-d'),
            'start_time' => '06:00:00',
            'end_time' => '22:00:00',
        ]);

        // Create 2 days of izin
        Presence::create([
            'user_id' => $mahasiswa->id,
            'attendance_id' => $attendance->id,
            'presence_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'presence_time' => '07:00:00',
            'status' => 'izin',
            'log_status' => 'izin',
        ]);
        Presence::create([
            'user_id' => $mahasiswa->id,
            'attendance_id' => $attendance->id,
            'presence_date' => Carbon::now()->subDays(1)->format('Y-m-d'),
            'presence_time' => '07:00:00',
            'status' => 'izin',
            'log_status' => 'izin',
        ]);

        $response = $this->actingAs($mahasiswa)->get(route('home.index'));
        $response->assertStatus(200);

        // Assert we see the Izin info
        $response->assertSee('Sedang Dalam Masa Izin');
        $response->assertSee('Izin Pulang (Surabaya)');
        $response->assertSee('Izin Keluar Asrama');
        $response->assertSee('2'); // 2 days of izin

        // Assert we DON'T see admin widget
        $response->assertDontSee('Total Pengguna');
        $response->assertDontSee('Pengguna Aktif');
    }
}
