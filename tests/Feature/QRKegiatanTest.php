<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\JadwalKegiatanAsrama;
use Illuminate\Support\Facades\Crypt;

class QRKegiatanTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_kegiatan_tanpa_jadwal_mengembalikan_error_yang_ramah(): void
    {
        $blok = \App\Models\BlokRuangan::create(['name' => 'Blok A']);
        $mahasiswa = User::factory()->create([
            'role_id' => 3,
            'password' => bcrypt('password123'),
            'asal_daerah' => 'Valid',
            'no_kamar' => '101',
            'blok_ruangan_id' => $blok->id
        ]);

        $admin = User::factory()->create(['role_id' => 1]); // Admin

        $payload = [
            'user_id' => $mahasiswa->id,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'scanner' => 'absensi'
        ];

        $encryptedPayload = Crypt::encryptString(json_encode($payload));

        $requestData = [
            'payload' => $encryptedPayload,
            'user_id' => null,
            'date' => null,
            'time' => null,
            'scanner' => null
        ];

        $response = $this->actingAs($admin)->post(route('admin.kameraKegiatanUpacaraApi'), $requestData);
        
        $response->assertStatus(302);
        // It should redirect back with error message
        $response->assertSessionHas('error');
        $errorMsg = session('error');
        $this->assertStringContainsString('Tidak Ada Kegiatan', $errorMsg);
        $this->assertStringNotContainsString('Attempt to read property', $errorMsg);
    }
}
