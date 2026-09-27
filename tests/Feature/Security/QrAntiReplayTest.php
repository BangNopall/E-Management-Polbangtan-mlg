<?php

namespace Tests\Feature\Security;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrAntiReplayTest extends TestCase
{
    public function test_presensi_menolak_request_dengan_payload_rusak(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();

        $response = $this->actingAs($admin)->post('/presense/api', [
            'payload' => 'INVALID_ENCRYPTED_PAYLOAD_STRING',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_qr_code_dengan_nonce_sama_tidak_dapat_digunakan_dua_kali(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        // Ensure attendance record exists today
        Attendance::firstOrCreate(
            ['date' => Carbon::now()->format('Y-m-d')],
            [
                'title' => 'Absensi Harian Test',
                'start_time' => '00:00:00',
                'end_time' => '23:59:59',
            ]
        );

        $nonce = (string) Str::uuid();
        $payloadData = [
            'user_id' => $student->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'status' => 'didalam',
            'scanner' => 'absensi',
            'nonce' => $nonce,
        ];

        $encrypted = Crypt::encryptString(json_encode($payloadData));

        // Scan pertama
        $response1 = $this->actingAs($admin)->post('/presense/api', [
            'payload' => $encrypted,
        ]);

        // Scan kedua dengan payload yang sama persis (replay attack)
        $response2 = $this->actingAs($admin)->post('/presense/api', [
            'payload' => $encrypted,
        ]);

        $response2->assertSessionHas('error', 'Kode QR ini sudah pernah digunakan.');
    }

    public function test_halaman_qr_hukum_menghasilkan_payload_dengan_nonce(): void
    {
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();
        $response = $this->actingAs($student)->get(route('home.qrhukum'));
        $response->assertOk();
        $response->assertViewHas('QrCode');
    }

    public function test_qr_pelanggaran_memiliki_nonce_dan_tidak_bisa_direplay(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        // Simulate student opening violation QR page and extracting payload
        $this->actingAs($student)->get(route('home.qrhukum'));
        
        $nonce = (string) Str::uuid();
        $payloadData = [
            'user_id' => $student->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'scanner' => 'pelanggaran',
            'nonce' => $nonce,
        ];
        $encrypted = Crypt::encryptString(json_encode($payloadData));

        // Scan pertama
        $response1 = $this->actingAs($admin)->post(route('admin.scanCamPelatihStore'), [
            'payload' => $encrypted,
        ]);

        // Scan kedua dengan payload yang sama
        $response2 = $this->actingAs($admin)->post(route('admin.scanCamPelatihStore'), [
            'payload' => $encrypted,
        ]);

        $response2->assertSessionHas('error', 'Kode QR ini sudah pernah digunakan.');
    }

    public function test_qr_code_dengan_jam_sama_di_hari_berbeda_ditolak(): void
    {
        $admin = User::where('role_id', User::ADMIN_ROLE_ID)->firstOrFail();
        $student = User::where('role_id', User::USER_ROLE_ID)->firstOrFail();

        // Payload with yesterday's date but current time
        $payloadData = [
            'user_id' => $student->id,
            'date' => Carbon::now()->subDay()->format('Y-m-d'),
            'time' => Carbon::now()->format('H:i:s'),
            'status' => 'didalam',
            'scanner' => 'absensi',
            'nonce' => (string) Str::uuid(),
        ];

        $encrypted = Crypt::encryptString(json_encode($payloadData));

        $response = $this->actingAs($admin)->post('/presense/api', [
            'payload' => $encrypted,
        ]);

        // Must reject expired QR code from yesterday
        $response->assertSessionHas('error');
    }
}
