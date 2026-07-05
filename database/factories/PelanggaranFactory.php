<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pelanggaran>
 */
class PelanggaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => random_int(3, 47),
            'jenis_pelanggaran_id' => random_int(1, 59),
            // 'date' => $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d'),
            'date' => $this->faker->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek())->format('Y-m-d'),
            'time' => $this->faker->time(),
        ];
    }
}
