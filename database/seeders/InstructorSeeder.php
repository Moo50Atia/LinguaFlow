<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\InstructorSlot;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        // Link all 'instructor' role users to an instructor profile
        User::where('role', 'instructor')->get()->each(function ($user) {
            $instructor = Instructor::factory()->create([
                'user_id' => $user->id,
                'bio' => "Professional instructor specializing in translation for the " . fake()->word() . " industry."
            ]);

            // Seed availability slots for the next 7 days
            for ($i = 0; $i < 7; $i++) {
                $date = now()->addDays($i);
                $times = ['09:00', '11:00', '14:00', '16:00'];
                
                foreach ($times as $time) {
                    InstructorSlot::factory()->create([
                        'instructor_id' => $instructor->id,
                        'date' => $date->format('Y-m-d'),
                        'time' => $time,
                        'is_booked' => fake()->boolean(25),
                    ]);
                }
            }
        });
    }
}
