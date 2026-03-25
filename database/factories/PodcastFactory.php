<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;

class PodcastFactory extends Factory
{
    protected $model = Podcast::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'audio_url' => 'podcasts/' . fake()->uuid() . '.mp3',
            'cover_image' => 'https://api.dicebear.com/7.x/shapes/svg?seed=' . fake()->word(),
            'duration' => fake()->randomElement(['15 min', '25 min', '40 min']),
            'category' => fake()->randomElement(['Medical', 'Legal', 'Business', 'General']),
            'language' => fake()->randomElement(['English', 'Arabic', 'Spanish']),
            'level' => fake()->randomElement(['A2', 'B1', 'B2', 'C1']),
            'instructor_id' => Instructor::factory(),
            'plays_count' => fake()->numberBetween(100, 10000),
            'is_premium' => fake()->boolean(30),
        ];
    }
}
