<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'order' => fake()->numberBetween(1, 10),
            'title' => fake()->sentence(4),
            'duration' => fake()->randomElement(['30 min', '45 min', '60 min', '1.5 hrs']),
            'description' => fake()->paragraph(),
            'notes' => fake()->text(),
            'image' => 'https://api.dicebear.com/7.x/shapes/svg?seed=' . fake()->word(),
            'has_quiz' => fake()->boolean(80),
        ];
    }
}
