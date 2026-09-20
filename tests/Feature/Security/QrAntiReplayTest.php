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
}
