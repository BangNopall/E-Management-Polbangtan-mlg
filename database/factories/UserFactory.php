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
            'blok_ruangan_id' => random_int(1, 5), // ASCII values for A to J (65 to 74)
            'no_kamar' => $this->faker->numerify('##'), // Generates a 2-digit number
            'kelas_id' => random_int(1, 36), // ASCII values for A to J (65 to 74)
            // Add other fields as needed
            'prodi_id' => random_int(1, 3), // Assuming there are 5 different programs
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
