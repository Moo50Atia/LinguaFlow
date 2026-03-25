<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonCompletionFactory extends Factory
{
    protected $model = LessonCompletion::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'lesson_id' => Lesson::factory(),
            'score' => fake()->numberBetween(60, 100),
            'passed' => true,
            'completed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
