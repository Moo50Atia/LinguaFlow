<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\Lesson;
use App\Models\LessonMaterial;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Instructor::all()->each(function ($instructor) {
            // Each instructor creates 1-3 courses
            Course::factory(fake()->numberBetween(1, 3))->create([
                'instructor_id' => $instructor->id,
            ])->each(function ($course) {
                // Create 5-8 lessons per course
                for ($i = 1; $i <= $course->total_lessons; $i++) {
                    $lesson = Lesson::factory()->create([
                        'course_id' => $course->id,
                        'order' => $i,
                        'title' => "Module $i: " . fake()->sentence(3),
                    ]);

                    // Each lesson has 1-2 materials
                    LessonMaterial::factory(fake()->numberBetween(1, 2))->create([
                        'lesson_id' => $lesson->id,
                    ]);

                    // Each lesson has 3-5 quiz questions
                    if ($lesson->has_quiz) {
                        QuizQuestion::factory(fake()->numberBetween(3, 5))->create([
                            'lesson_id' => $lesson->id,
                            'type' => 'lesson_quiz',
                        ]);
                    }
                }

                // Add a final assessment for the course
                QuizQuestion::factory(5)->courseAssessment()->create([
                    'course_id' => $course->id,
                ]);
            });
        });
    }
}
