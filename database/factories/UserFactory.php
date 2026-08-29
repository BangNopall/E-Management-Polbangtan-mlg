<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'nim' => $this->faker->numerify('##########'), // Generates a 10-digit number
            'blok_ruangan_id' => \App\Models\BlokRuangan::inRandomOrder()->value('id') ?? 1,
            'no_kamar' => $this->faker->numerify('##'), // Generates a 2-digit number
            'kelas_id' => \App\Models\Kelas::inRandomOrder()->value('id') ?? 1,
            // Add other fields as needed
            'prodi_id' => \App\Models\Prodi::inRandomOrder()->value('id') ?? 1,
            'asal_daerah' => $this->faker->city,
            'no_hp' => $this->faker->numerify('###########'), // Generates a 12-digit number
            'password' => bcrypt('password'), // Default password is 'password'
            'role_id' => 3, // Assuming there are 3 different roles
            // 'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
