<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Determine if the user can update the course.
     */
    public function update(User $user, Course $course): bool
    {
        return $course->instructor->user_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine if the user can delete the course.
     */
    public function delete(User $user, Course $course): bool
    {
        return $course->instructor->user_id === $user->id || $user->role === 'admin';
    }
}
