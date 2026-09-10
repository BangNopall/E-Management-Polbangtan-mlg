<?php

namespace Tests\Feature\Izin;

use App\Models\PengajuanIzin;
use App\Models\User;
use App\Models\JenisIzin;
use App\Models\Kelas;
use App\Models\Prodi;
use App\Models\LevelKelas;
use App\Models\BlokRuangan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IzinBugFixTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;
    protected JenisIzin $jenisIzin;

    protected function setUp(): void
    {
        parent::setUp();
        
        $prodi = Prodi::create(['prodi' => 'Teknologi Pertanian']);
        $levelKelas = LevelKelas::create(['nama_level_kelas' => 'Tingkat 1']);
        $kelas = Kelas::create(['kelas' => 'TP-1A', 'nama_kelas' => 'TP-1A', 'prodi_id' => $prodi->id, 'level_kelas_id' => $levelKelas->id]);
        $blok = BlokRuangan::create(['name' => 'Blok A']);

        $this->student = User::factory()->create([
            'role_id' => 3,
            'status' => 'didalam',
            'no_kamar' => '1',
            'blok_ruangan_id' => $blok->id,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'nim' => '123456',
            'no_hp' => '08123456789',
            'password' => bcrypt('newpassword'),
            'asal_daerah' => 'Malang',
            'email' => 'student@test.com',
        ]);
        
        $this->jenisIzin = JenisIzin::create([
            'kode' => 'TEST',
            'nama' => 'Test Izin',
            'is_active' => true,
        ]);
    }

    public function test_overlapping_izin_dengan_waktu_berurutan_diperbolehkan(): void
    {
        PengajuanIzin::create([
            'user_id' => $this->student->id,
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Izin Pertama',
            'tujuan_lokasi' => 'Test',
            'nama_snapshot' => $this->student->name,
            'status' => 'disetujui',
            'waktu_berangkat' => Carbon::today()->addHours(10),
            'waktu_kembali' => Carbon::today()->addHours(12),
        ]);
        
        $payload = [
            'jenis_izin_id' => $this->jenisIzin->id,
            'keperluan' => 'Test Berurutan',
            'tujuan_lokasi' => 'Test',
            'waktu_berangkat' => Carbon::today()->addHours(12)->toDateTimeString(),
            'waktu_kembali' => Carbon::today()->addHours(14)->toDateTimeString(),
        ];

        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);
    }
}
