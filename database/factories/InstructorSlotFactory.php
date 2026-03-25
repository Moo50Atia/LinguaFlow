<?php

namespace Database\Factories;

use App\Models\Instructor;
use App\Models\InstructorSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstructorSlotFactory extends Factory
{
    protected $model = InstructorSlot::class;

    public function definition(): array
    {
        return [
            'instructor_id' => Instructor::factory(),
            'date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'time' => fake()->randomElement(['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']),
            'is_booked' => fake()->boolean(20),
        ];
    }
}
