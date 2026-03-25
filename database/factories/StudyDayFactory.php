<?php

namespace Database\Factories;

use App\Models\StudyDay;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyDayFactory extends Factory
{
    protected $model = StudyDay::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'minutes_studied' => fake()->numberBetween(15, 120),
        ];
    }
}
