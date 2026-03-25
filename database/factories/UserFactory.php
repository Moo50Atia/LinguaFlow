<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'avatar' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . fake()->userName(),
            'bio' => fake()->sentence(),
            'gender' => fake()->randomElement(['male', 'female']),
            'date_of_birth' => fake()->date('Y-m-d', '-18 years'),
            'location' => fake()->city() . ', ' . fake()->country(),
            'native_language' => fake()->randomElement(['English', 'Arabic', 'Spanish', 'French']),
            'cefr_level' => fake()->randomElement(['A1.1', 'A1.2', 'A2', 'B1.1', 'B1.2', 'B2', 'C1', 'C2']),
            'role' => 'student',
            'is_vip' => fake()->boolean(20),
            'is_online' => fake()->boolean(30),
            'last_seen_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Indicate that the user is an instructor.
     */
    public function instructor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'instructor',
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
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
