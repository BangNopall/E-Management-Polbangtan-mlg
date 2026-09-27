<?php

namespace Tests\Feature\Security;

use App\Models\BlokRuangan;
use App\Models\Kelas;
use App\Models\LevelKelas;
use App\Models\Prodi;
use App\Models\Ukm;
use App\Models\UkmJadwal;
use App\Models\UkmMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrMandatoryPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-10-01 08:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_scanner_kegiatan_menolak_request_tanpa_payload(): void
    {
        $operator = User::factory()->create([
            'role_id' => User::OPERATOR_ROLE_ID,
        ]);

        $student = User::factory()->create([
            'role_id' => User::USER_ROLE_ID,
        ]);

        // Attempt to submit attendance with raw parameters but omitting 'payload'
        $response = $this->actingAs($operator)->post(route('admin.kameraKegiatanUpacaraApi'), [
            'user_id' => $student->id,
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString(),
            'scanner' => 'absensi',
        ]);

        // Must fail with error and NOT record attendance
        $response->assertSessionHas('error', 'Payload QR Code wajib disertakan.');
        $this->assertDatabaseMissing('presensi_upacaras', [
            'user_id' => $student->id,
            'status_kehadiran' => 'Hadir',
        ]);
    }

    public function test_scanner_ukm_menolak_request_tanpa_payload(): void
    {
        $pelatih = User::factory()->create(['role_id' => User::PELATIH_ROLE_ID]);
        $student = User::factory()->create(['role_id' => User::USER_ROLE_ID]);
        $ukm = Ukm::create(['nama' => 'UKM Silat', 'slug' => 'ukm-silat']);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $student->id,
            'peran' => 'anggota',
            'status' => 'aktif',
        ]);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $pelatih->id,
            'peran' => 'pelatih',
            'status' => 'aktif',
        ]);

        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Silat',
            'jenis' => 'latihan',
            'tanggal' => Carbon::now()->toDateString(),
            'mulai_acara' => '07:00:00',
            'selesai_acara' => '09:00:00',
            'status_verifikasi' => 'disetujui',
            'created_by' => $pelatih->id,
        ]);

        // Submit raw parameters without encrypted payload
        $response = $this->actingAs($pelatih)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'user_id' => $student->id,
            'date' => Carbon::now()->toDateString(),
            'time' => Carbon::now()->toTimeString(),
            'scanner' => 'absensi',
        ]);

        // Must reject missing payload
        $response->assertSessionHas('error', 'Payload QR Code wajib disertakan.');
    }
}
