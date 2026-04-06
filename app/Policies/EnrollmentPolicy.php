<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class EnrollmentPolicy
{
    /**
     * Determine if the user can enroll in a course.
     */
    public function create(User $user, Course $course): bool
    {
        // Must not be already enrolled
        return !$course->enrollments()->where('user_id', $user->id)->exists();
    }
}
