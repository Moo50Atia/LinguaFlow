<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['booking_confirmed', 'new_correction', 'chat_message', 'course_enrolled']),
            'title' => fake()->sentence(3),
            'body' => fake()->sentence(10),
            'icon' => fake()->randomElement(['bell', 'message', 'book', 'check']),
            'data' => ['url' => '/dashboard'],
            'read_at' => fake()->boolean(40) ? now() : null,
        ];
    }
}
