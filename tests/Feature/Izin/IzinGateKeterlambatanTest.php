<?php

namespace Tests\Feature\Izin;

use App\Models\Attendance;
use App\Models\BlokRuangan;
use App\Models\JenisIzin;
use App\Models\JenisPelanggaran;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Pelanggaran;
use App\Models\PengajuanIzin;
use App\Models\Presence;
use App\Models\Prodi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class IzinGateKeterlambatanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected JenisIzin $jenisIzin;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => User::ADMIN_ROLE_ID,
            'name' => 'Admin Gate',
        ]);

        $prodi = Prodi::create(['prodi' => 'Teknik']);
        $level = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create([
            'kelas' => 'TK-1',
            'nama_kelas' => 'TK-1',
            'prodi_id' => $prodi->id,
            'level_kelas_id' => $level->id,
        ]);
        $blok = BlokRuangan::create(['name' => 'Blok B']);

        $this->student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'blok_ruangan_id' => $blok->id,
            'status' => 'izin',
        ]);

        $this->jenisIzin = JenisIzin::create([
            'nama' => 'Izin Dinas',
            'kode' => 'IDN',
            'is_active' => true,
        ]);

        // Buat jenis pelanggaran terlambat
        JenisPelanggaran::create([
            'jenis_pelanggaran' => 'Terlambat kembali dari izin resmi',
            'kategori_id' => 1,
            'poin' => 2,
            'sub_kategori' => 'Ringan',
        ]);

        $this->attendance = Attendance::create([
            'title' => 'Absensi Harian',
            'date' => Carbon::now()->toDateString(),
            'start_time' => '06:00:00',
            'end_time' => '22:00:00',
        ]);
    }

    public function test_scan_masuk_melewati_tenggat_izin_mengubah_presence_log_status_ke_telat(): void
    {
        $now = Carbon::now();

        // Izin yang tenggat kembalinya 1 jam yang lalu (terlambat)
        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Lomba',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => $now->copy()->subHours(6),
            'waktu_kembali' => $now->copy()->subHour(1), // Lewat 1 jam
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'keluar_at' => $now->copy()->subHours(6),
        ]);

        // Catat presence keluar awal hari ini
        Presence::create([
            'user_id' => $this->student->id,
            'attendance_id' => $this->attendance->id,
            'presence_date' => $now->toDateString(),
            'presence_keluar' => $now->copy()->subHours(6)->format('H:i:s'),
            'log_status' => 'izin',
        ]);

        $payload = Crypt::encryptString(json_encode([
            'user_id' => $this->student->id,
            'date' => $now->toDateString(),
            'time' => $now->format('H:i:s'),
            'status' => 'didalam',
            'scanner' => 'absensi',
        ]));

        $response = $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'payload' => $payload,
        ]);

        $response->assertRedirect(route('admin.kamera'));
        $response->assertSessionHas('error'); // Flash error memuat keterangan keterlambatan

        $pengajuan->refresh();
        $this->assertEquals('terlambat', $pengajuan->status);

        // Pastikan Presence log_status adalah 'telat' dan is_late = 1
        $presence = Presence::where('user_id', $this->student->id)->latest()->first();
        $this->assertNotNull($presence);
        $this->assertEquals('telat', $presence->log_status);
        $this->assertEquals(1, $presence->is_late);

        // Status user didalam karena sudah fisik masuk asrama
        $this->assertEquals('didalam', $this->student->fresh()->status);
    }

    public function test_scan_masuk_tepat_waktu_mengubah_presence_log_status_ke_didalam(): void
    {
        $now = Carbon::now();

        // Izin yang tenggat kembalinya 2 jam ke depan (tepat waktu)
        $pengajuan = PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Lomba',
            'tujuan_lokasi' => 'Surabaya',
            'waktu_berangkat' => $now->copy()->subHours(3),
            'waktu_kembali' => $now->copy()->addHours(2), // Belum lewat
            'nama_snapshot' => $this->student->name,
            'status' => 'berjalan',
            'keluar_at' => $now->copy()->subHours(3),
        ]);

        Presence::create([
            'user_id' => $this->student->id,
            'attendance_id' => $this->attendance->id,
            'presence_date' => $now->toDateString(),
            'presence_keluar' => $now->copy()->subHours(3)->format('H:i:s'),
            'log_status' => 'izin',
        ]);

        $payload = Crypt::encryptString(json_encode([
            'user_id' => $this->student->id,
            'date' => $now->toDateString(),
            'time' => $now->format('H:i:s'),
            'status' => 'didalam',
            'scanner' => 'absensi',
        ]));

        $response = $this->actingAs($this->admin)->post(route('admin.presense.api'), [
            'payload' => $payload,
        ]);

        $response->assertRedirect(route('admin.kamera'));
        $response->assertSessionHas('success');

        $pengajuan->refresh();
        $this->assertEquals('selesai', $pengajuan->status);

        $presence = Presence::where('user_id', $this->student->id)->latest()->first();
        $this->assertNotNull($presence);
        $this->assertEquals('didalam', $presence->log_status);
        $this->assertEquals(0, $presence->is_late);
        $this->assertEquals('didalam', $this->student->fresh()->status);
    }
}
