<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'current_lesson' => fake()->numberBetween(1, 5),
            'completed_lessons' => fake()->numberBetween(0, 5),
            'progress' => fake()->randomFloat(2, 0, 100),
            'status' => fake()->randomElement(['active', 'completed', 'dropped']),
        ];
    }
}
