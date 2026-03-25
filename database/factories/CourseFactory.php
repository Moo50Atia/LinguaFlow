<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'instructor_id' => Instructor::factory(),
            'title' => fake()->sentence(3),
            'level' => fake()->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'language' => fake()->randomElement(['English', 'Spanish', 'French', 'German']),
            'language_flag' => fake()->randomElement(['🇬🇧', '🇪🇸', '🇫🇷', '🇩🇪']),
            'total_lessons' => fake()->numberBetween(5, 20),
            'price' => fake()->randomElement(['Free', '$49', '$99', '$149', '$199']),
            'image' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . fake()->word(),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(['Medical', 'Legal', 'Business', 'Literary', 'Technical', 'Interpretation', 'General']),
            'is_published' => true,
            'enrolled_count' => fake()->numberBetween(0, 500),
        ];
    }
}
