<?php

namespace App\Actions\Learning;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompleteLessonAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(User $user, Lesson $lesson, ?int $score = null): LessonCompletion
    {
        return DB::transaction(function () use ($user, $lesson, $score) {

            // 1. Mark lesson as completed
            $completion = LessonCompletion::firstOrCreate([
                'user_id'   => $user->id,
                'lesson_id' => $lesson->id,
            ], [
                'score' => $score // if lesson had a quiz
            ]);

            // 2. Load Enrollment
            $enrollment = Enrollment::where('user_id', $user->id)
                                    ->where('course_id', $lesson->course_id)
                                    ->firstOrFail();

            // 3. Compute new progress
            $course = $lesson->course;
            $totalLessons = $course->lessons()->count();
            $completedLessons = LessonCompletion::where('user_id', $user->id)
                ->whereHas('lesson', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })->count();

            $progress = $totalLessons > 0 ? (int)(($completedLessons / $totalLessons) * 100) : 0;

            // 4. Update timeline: move current_lesson_id to next lesson
            $nextLesson = $course->lessons()->where('order', '>', $lesson->order)->orderBy('order')->first();

            $enrollment->update([
                'progress'          => $progress,
                'current_lesson_id' => $nextLesson?->id ?? $lesson->id,
                'status'            => $progress === 100 ? 'completed' : 'active',
            ]);

            // 5. Generate Certificate if 100% complete
            if ($progress === 100 && !$user->certificates()->where('title', 'like', "%{$course->title}%")->exists()) {
                Certificate::create([
                    'user_id'            => $user->id,
                    'title'              => "Completion of {$course->title}",
                    'certificate_number' => strtoupper(Str::random(10)),
                    'level'              => $course->level,
                    'category'           => $course->category,
                    'issued_at'          => now(),
                ]);

                $this->notificationService->create(
                    $user->id,
                    'certificate_earned',
                    'Course Completed!',
                    "Congratulations! You completed {$course->title} and earned a certificate."
                );
            }

            return $completion;
        });
    }
}
