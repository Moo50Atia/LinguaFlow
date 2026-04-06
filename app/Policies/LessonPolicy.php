<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;
use App\Models\Course;

class LessonPolicy
{
    /**
     * Determine if the user can view the lesson (must be enrolled).
     */
    public function view(User $user, Lesson $lesson): bool
    {
        return $lesson->course->enrollments()->where('user_id', $user->id)->exists() || 
               $lesson->course->instructor->user_id === $user->id || 
               $user->role === 'admin';
    }

    /**
     * Determine if the user can create a lesson for the given course.
     */
    public function create(User $user, Course $course): bool
    {
        return $course->instructor->user_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine if the user can update the given lesson.
     */
    public function update(User $user, Lesson $lesson): bool
    {
        return $lesson->course->instructor->user_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine if the user can delete the given lesson.
     */
    public function delete(User $user, Lesson $lesson): bool
    {
        return $lesson->course->instructor->user_id === $user->id || $user->role === 'admin';
    }
}
