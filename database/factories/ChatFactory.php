<?php

namespace Database\Factories;

use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatFactory extends Factory
{
    protected $model = Chat::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['direct', 'group']);
        return [
            'type' => $type,
            'name' => $type === 'group' ? fake()->words(3, true) : null,
            'avatar' => $type === 'group' ? 'https://api.dicebear.com/7.x/identicon/svg?seed=' . fake()->word() : null,
        ];
    }
}
