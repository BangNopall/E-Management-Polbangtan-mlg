<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the full DatabaseSeeder runs successfully.
     */
    public function test_database_seeder_runs_successfully(): void
    {
        // This will run DatabaseSeeder which includes the DummyDataSeeder in the testing environment
        $this->artisan('db:seed')
             ->assertSuccessful();

        // Verify some tables have data
        $this->assertTrue(\App\Models\User::count() >= 53); // 3 manual devs + 50 dummy + seeds
        $this->assertTrue(\App\Models\Pelanggaran::count() > 0);
    }
    
    /**
     * Test that the DummyDataSeeder runs successfully isolated.
     */
    public function test_dummy_data_seeder_runs_successfully(): void
    {
        // First run necessary foundational seeders required for DummyData
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\ProdiSeeder::class);
        $this->seed(\Database\Seeders\BlokRuanganSeeder::class);
        $this->seed(\Database\Seeders\KelasSeeder::class);
        $this->seed(\Database\Seeders\KategoriPelanggaranSeeder::class);
        $this->seed(\Database\Seeders\JenisPelanggaranSeeder::class);

        // Run the DummyDataSeeder
        $this->seed(\Database\Seeders\DummyDataSeeder::class);

        // Check if dummy users are created
        $this->assertDatabaseHas('users', [
            'email' => 'user_dev@polbangtanmalang.ac.id'
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'operator_dev@polbangtanmalang.ac.id'
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'pelatih_dev@polbangtanmalang.ac.id'
        ]);
    }
}
