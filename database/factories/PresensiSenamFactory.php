<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PresensiSenam>
 */
class PresensiSenamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jadwalKegiatanAsrama_id' => \App\Models\JadwalKegiatanAsrama::inRandomOrder()->value('id') ?? 1,
            'user_id' => \App\Models\User::inRandomOrder()->value('id') ?? 1,
            'jam_kehadiran' => $this->faker->time(),
            'status_kehadiran' => $this->faker->randomElement(['Hadir', 'Alpha', 'Izin']),

        ];
    }
}
