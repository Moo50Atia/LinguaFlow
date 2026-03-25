<?php

namespace Database\Factories;

use App\Models\Moment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MomentFactory extends Factory
{
    protected $model = Moment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
            'category' => fake()->randomElement([
                'General', 'Grammar', 'Vocabulary', 'Culture',
                'Pronunciation', 'Questions', 'Advice', 'Daily Life'
            ]),
            'images' => [
                'https://api.dicebear.com/7.x/pixel-art/svg?seed=' . fake()->word(),
            ],
            'likes_count' => fake()->numberBetween(0, 100),
            'comments_count' => fake()->numberBetween(0, 20),
        ];
    }
}
