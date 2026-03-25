<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizResultFactory extends Factory
{
    protected $model = QuizResult::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'lesson_id' => Lesson::factory(),
            'course_id' => null,
            'quiz_title' => fake()->sentence(3),
            'course_name' => fake()->sentence(2),
            'score' => fake()->numberBetween(50, 100),
            'total_questions' => 10,
            'passed' => fake()->boolean(70),
            'type' => 'lesson_quiz',
        ];
    }
}
