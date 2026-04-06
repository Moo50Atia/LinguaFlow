<?php

namespace App\Actions\Learning;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollStudentAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(User $user, int $courseId): Enrollment
    {
        return DB::transaction(function () use ($user, $courseId) {
            $course = Course::findOrFail($courseId);

            // Prevent duplicate enrollment
            if (Enrollment::where('user_id', $user->id)->where('course_id', $courseId)->exists()) {
                throw ValidationException::withMessages([
                    'course_id' => ['You are already enrolled in this course.']
                ]);
            }

            // Create Enrollment
            $enrollment = Enrollment::create([
                'user_id'           => $user->id,
                'course_id'         => $courseId,
                'current_lesson_id' => $course->lessons()->orderBy('order')->first()?->id,
                'progress'          => 0,
                'status'            => 'active',
            ]);

            // Increment Course Enrolled Count
            $course->increment('total_students'); // Assumes we cache this or we can just let relations count

            // Send Welcome Notification
            $this->notificationService->create(
                userId: $user->id,
                type: 'course_enrollment',
                title: "Welcome to {$course->title}!",
                body: "You have successfully enrolled. You can start your first lesson now."
            );

            return $enrollment;
        });
    }
}
