<?php

namespace Database\Factories;

use App\Models\Presence;
use Illuminate\Database\Eloquent\Factories\Factory;

class PresenceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Presence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Example data generation
        return [
            'user_id' => $this->faker->numberBetween(3, 50), // Replace with your logic to generate user_id
            'attendance_id' => $this->faker->numberBetween(1, 200), // Replace with your logic to generate attendance_id
            'presence_date' => $this->faker->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek())->format('Y-m-d'),
            // 'presence_date' => $this->faker->dateTimeBetween(now()->startOfYear(), now()->endOfYear())->format('Y-m-d'),
            'presence_masuk' => $this->faker->time(),
            'presence_keluar' => $this->faker->time(),
            'is_late' => $this->faker->boolean,
            'is_active' => $this->faker->boolean,
            'log_status' => $this->faker->randomElement(['diluar', 'telat', 'didalam']),
        ];
    }
}
