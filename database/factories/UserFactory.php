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
        $kelas = \App\Models\Kelas::inRandomOrder()->first();
        $prodiId = $kelas?->prodi_id ?? (\App\Models\Prodi::inRandomOrder()->value('id') ?? 1);

        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'nim' => $this->faker->numerify('##########'), // Generates a 10-digit number
            'blok_ruangan_id' => \App\Models\BlokRuangan::inRandomOrder()->value('id') ?? 1,
            'no_kamar' => $this->faker->numerify('##'), // Generates a 2-digit number
            'kelas_id' => $kelas?->id ?? 1,
            'prodi_id' => $prodiId,
            'asal_daerah' => $this->faker->city,
            'no_hp' => $this->faker->unique()->numerify('###########'), // Generates a 11-digit number
            'password' => bcrypt('password'), // Default password is 'password'
            'is_password_changed' => 1,
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
