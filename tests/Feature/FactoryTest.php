<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run foundational seeders to avoid foreign key violations in factories
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ProdiSeeder::class);
        $this->seed(\Database\Seeders\BlokRuanganSeeder::class);
        $this->seed(\Database\Seeders\KelasSeeder::class);
        $this->seed(\Database\Seeders\KategoriPelanggaranSeeder::class);
        $this->seed(\Database\Seeders\JenisPelanggaranSeeder::class);
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = \App\Models\User::factory()->create();
        
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertNotNull($user->blok_ruangan_id);
        $this->assertNotNull($user->kelas_id);
    }

    public function test_presence_factory_creates_valid_presence(): void
    {
        // First create parent models explicitly or let the factory fallback to random
        $user = \App\Models\User::factory()->create();
        $attendance = \App\Models\Attendance::factory()->create();

        $presence = \App\Models\Presence::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);
        
        $this->assertDatabaseHas('presences', ['id' => $presence->id]);
    }

    public function test_pelanggaran_factory_creates_valid_record(): void
    {
        $user = \App\Models\User::factory()->create();
        $pelanggaran = \App\Models\Pelanggaran::factory()->create([
            'user_id' => $user->id
        ]);
        
        $this->assertDatabaseHas('pelanggarans', ['id' => $pelanggaran->id]);
    }

    public function test_jadwal_kegiatan_asrama_factory_creates_valid_record(): void
    {
        $jadwal = \App\Models\JadwalKegiatanAsrama::factory()->create();
        
        $this->assertDatabaseHas('jadwal_kegiatan_asramas', ['id' => $jadwal->id]);
    }
}
