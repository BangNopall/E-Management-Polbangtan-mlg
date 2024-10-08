<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\jadwalKegiatanAsrama;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\jadwalKegiatanAsrama>
 */
class jadwalKegiatanAsramaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tanggal_kegiatan' => $this->faker->unique(1000)->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek())->format('Y-m-d'),
            'blok_id' => $this->faker->numberBetween(3, 5),
            'jenis_kegiatan' => $this->faker->randomElement(['Apel', 'Upacara', 'Senam']),
            'mulai_acara' => $this->faker->time(),
            'selesai_acara' => $this->faker->time(),
        ];
    }
}
