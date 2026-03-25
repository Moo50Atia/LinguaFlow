<?php

namespace Database\Factories;

use App\Models\Chat;
use App\Models\ChatMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMemberFactory extends Factory
{
    protected $model = ChatMember::class;

    public function definition(): array
    {
        return [
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['admin', 'member']),
            'unread_count' => fake()->numberBetween(0, 10),
        ];
    }
}
