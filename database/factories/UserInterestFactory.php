<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserInterestFactory extends Factory
{
    protected $model = UserInterest::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'interest' => fake()->randomElement([
                'Travel', 'Music', 'Culture', 'Literature', 'Technology',
                'Medicine', 'Law', 'Business', 'Art', 'History', 'Food',
                'Sports', 'Science', 'Politics', 'Gaming', 'Writing'
            ]),
        ];
    }
}
