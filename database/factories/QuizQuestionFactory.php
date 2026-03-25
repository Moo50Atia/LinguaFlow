<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    public function definition(): array
    {
        $options = [
            fake()->sentence(),
            fake()->sentence(),
            fake()->sentence(),
            fake()->sentence(),
        ];

        return [
            'lesson_id' => Lesson::factory(),
            'course_id' => null,
            'type' => 'lesson_quiz',
            'question' => fake()->sentence() . '?',
            'options' => $options,
            'correct_answer' => fake()->numberBetween(0, 3),
            'order' => fake()->numberBetween(1, 5),
        ];
    }

    public function courseAssessment(): static
    {
        return $this->state(fn (array $attributes) => [
            'lesson_id' => null,
            'course_id' => Course::factory(),
            'type' => 'final_assessment',
        ]);
    }
}
