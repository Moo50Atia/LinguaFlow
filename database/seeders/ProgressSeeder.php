<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\StudyDay;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $instructors = Instructor::all();

        foreach ($students as $student) {
            // 1. Enroll in courses
            $courses = \App\Models\Course::all()->random(fake()->numberBetween(1, 2));
            foreach ($courses as $course) {
                $enrollment = Enrollment::factory()->create([
                    'user_id' => $student->id,
                    'course_id' => $course->id,
                ]);

                // Complete some lessons
                $completedCount = fake()->numberBetween(1, 3);
                $lessons = $course->lessons->take($completedCount);
                foreach ($lessons as $lesson) {
                    LessonCompletion::factory()->create([
                        'enrollment_id' => $enrollment->id,
                        'lesson_id' => $lesson->id,
                    ]);
                }

                // If course is completed, issue certificate
                if ($enrollment->status === 'completed') {
                    Certificate::factory()->create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                    ]);
                }
            }

            // 2. Add study activity (Heatmap)
            for ($i = 0; $i < 20; $i++) {
                StudyDay::factory()->create([
                    'user_id' => $student->id,
                    'date' => now()->subDays(fake()->numberBetween(0, 90))->format('Y-m-d'),
                ]);
            }

            // 3. Make some bookings
            for ($i = 0; $i < 2; $i++) {
                $instructor = $instructors->random();
                $slot = $instructor->slots()->where('is_booked', false)->first();
                if ($slot) {
                    Booking::factory()->create([
                        'user_id' => $student->id,
                        'instructor_id' => $instructor->id,
                        'instructor_slot_id' => $slot->id,
                        'status' => 'confirmed',
                    ]);
                    $slot->update(['is_booked' => true]);
                }
            }

            // 4. Get a subscription
            Subscription::factory()->create([
                'user_id' => $student->id,
            ]);
        }
    }
}
