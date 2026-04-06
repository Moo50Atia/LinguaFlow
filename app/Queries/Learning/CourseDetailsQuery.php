<?php

namespace App\Queries\Learning;

use App\Models\Course;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CourseDetailsQuery
{
    /**
     * Get the full details of a course including lessons and user progress.
     *
     * @param int $courseId
     * @param int|null $userId
     * @return array
     */
    public function execute(int $courseId, ?int $userId = null): array
    {
        $course = Course::with(['instructor.user', 'lessons' => function ($query) {
            $query->orderBy('order')
                  ->withCount('materials')
                  ->withExists('quizzes');
        }])->findOrFail($courseId);

        // Append user specific progress if authenticated
        $userEnrollment = null;
        $completedLessonIds = [];

        if ($userId) {
            $userEnrollment = $course->enrollments()->where('user_id', $userId)->first();
            
            if ($userEnrollment) {
                $completedLessonIds = \App\Models\LessonCompletion::where('user_id', $userId)
                    ->whereIn('lesson_id', $course->lessons->pluck('id'))
                    ->pluck('lesson_id')
                    ->toArray();
            }
        }

        // Map lesson states (locked, unlocked, completed)
        $lessonsMapped = $course->lessons->map(function ($lesson) use ($userEnrollment, $completedLessonIds) {
            $status = 'locked';

            if (!$userEnrollment) {
                // Guests see everything as locked except maybe first lesson if trial?
                $status = 'locked';
            } else {
                if (in_array($lesson->id, $completedLessonIds)) {
                    $status = 'completed';
                } elseif ($userEnrollment->current_lesson_id === $lesson->id || $lesson->order === 1) {
                    $status = 'unlocked';
                }
            }

            return [
                'id'              => $lesson->id,
                'title'           => $lesson->title,
                'order'           => $lesson->order,
                'duration'        => $lesson->duration,
                'status'          => $status,
                'has_quiz'        => clone (bool) $lesson->quizzes_exists,
                'materials_count' => $lesson->materials_count,
            ];
        });

        return [
            'id'             => $course->id,
            'title'          => $course->title,
            'level'          => $course->level,
            'language'       => $course->language,
            'price'          => $course->price,
            'category'       => $course->category,
            'description'    => $course->description,
            'image'          => $course->image ? url($course->image) : null,
            'total_lessons'  => $course->lessons->count(),
            'enrolled_count' => $course->total_students ?? 0,
            'instructor'     => [
                'id'   => $course->instructor->id,
                'name' => $course->instructor->user->name,
                'bio'  => $course->instructor->bio,
            ],
            'enrollment'     => $userEnrollment ? [
                'progress'          => $userEnrollment->progress,
                'current_lesson_id' => $userEnrollment->current_lesson_id,
            ] : null,
            'lessons'        => $lessonsMapped,
        ];
    }
}
