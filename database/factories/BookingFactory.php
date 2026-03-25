<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Instructor;
use App\Models\InstructorSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'instructor_id' => Instructor::factory(),
            'instructor_slot_id' => InstructorSlot::factory(),
            'booking_type' => fake()->randomElement(['complete-course', 'specific-session']),
            'course_style' => fake()->randomElement(['private', 'group']),
            'date' => fake()->dateTimeBetween('now', '+1 month'),
            'time' => fake()->time('H:i'),
            'price' => fake()->randomFloat(2, 20, 200),
            'status' => fake()->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'notes' => fake()->sentence(),
        ];
    }
}
