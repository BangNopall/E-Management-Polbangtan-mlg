<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ukm;
use App\Models\UkmMember;
use App\Models\UkmJadwal;
use App\Models\UkmPresensi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class UkmScanDecryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_scanner_ukm_berhasil_mendekripsi_dan_melakukan_presensi(): void
    {
        $pembina = User::factory()->create(['role_id' => User::PEMBINA_ROLE_ID]);
        $mahasiswa = User::factory()->create(['role_id' => User::USER_ROLE_ID]);

        $ukm = Ukm::create(['nama' => 'UKM Catur', 'slug' => 'ukm-catur', 'is_active' => true]);
        
        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $pembina->id,
            'peran' => 'pembina',
            'status' => 'aktif'
        ]);

        UkmMember::create([
            'ukm_id' => $ukm->id,
            'user_id' => $mahasiswa->id,
            'peran' => 'anggota',
            'status' => 'aktif'
        ]);

        $timeMulai = Carbon::now()->subMinutes(10)->format('H:i:s');
        $timeSelesai = Carbon::now()->addMinutes(10)->format('H:i:s');
        $jadwal = UkmJadwal::create([
            'ukm_id' => $ukm->id,
            'judul' => 'Latihan Rutin',
            'deskripsi' => 'Latihan Rutin',
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'mulai_acara' => $timeMulai,
            'selesai_acara' => $timeSelesai,
            'status_verifikasi' => 'disetujui'
        ]);

        // Mock Payload dari QRCode (format enkripsi)
        $validateQR = [
            'user_id' => $mahasiswa->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'scanner' => 'absensi'
        ];
        
        $jsonRaw = json_encode($validateQR);
        $encryptedPayload = Crypt::encryptString($jsonRaw);

        // Simulasi JS mengirim payload terenkripsi dan field legacy yang kosong/null
        $response = $this->actingAs($pembina)->post(route('admin.ukm.scan.store', $jadwal->id), [
            'payload' => $encryptedPayload,
            'user_id' => null,
            'date' => null,
            'time' => null,
            'scanner' => null
        ]);

        $response->assertRedirect(route('admin.ukm.scan.show', $jadwal->id));
        $response->assertSessionHas('success');

        // Pastikan presensi berubah menjadi Hadir
        $this->assertDatabaseHas('ukm_presensis', [
            'ukm_jadwal_id' => $jadwal->id,
            'user_id' => $mahasiswa->id,
            'status_kehadiran' => 'Hadir'
        ]);
    }
}
