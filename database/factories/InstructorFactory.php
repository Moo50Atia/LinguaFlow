<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorFactory extends Factory
{
    protected $model = Instructor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->instructor(),
            'category' => fake()->randomElement(['Medical', 'Legal', 'Business', 'Literary', 'Technical', 'Interpretation']),
            'type' => fake()->randomElement(['Paid', 'Free']),
            'price_per_hour' => fake()->randomFloat(2, 10, 100),
            'bio' => fake()->paragraphs(2, true),
            'specialties' => fake()->randomElements([
                'Simultaneous Interpretation', 'Legal Documentation', 'Medical Reports',
                'Business Correspondence', 'Technical Manuals', 'Literary Translation'
            ], 3),
            'schedule' => fake()->randomElement(['Mon-Fri', 'Weekends', 'Evening Slots', 'Flexible']),
            'years_experience' => fake()->numberBetween(1, 20),
            'total_students' => fake()->numberBetween(50, 5000),
            'rating' => fake()->randomFloat(2, 3.5, 5.0),
            'total_reviews' => fake()->numberBetween(10, 500),
        ];
    }
}
