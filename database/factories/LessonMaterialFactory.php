<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonMaterial;
use Illuminate\Database\Eloquent\Factories\Factory;

class LessonMaterialFactory extends Factory
{
    protected $model = LessonMaterial::class;

    public function definition(): array
    {
        $types = ['PDF', 'DOC', 'MP3', 'XLS'];
        $type = fake()->randomElement($types);

        return [
            'lesson_id' => Lesson::factory(),
            'name' => fake()->word() . '.' . strtolower($type),
            'type' => $type,
            'file_path' => 'materials/' . fake()->uuid() . '.' . strtolower($type),
            'size' => fake()->numberBetween(1, 10) . '.' . fake()->numberBetween(0, 9) . ' MB',
        ];
    }
}
