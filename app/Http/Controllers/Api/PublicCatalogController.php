<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PublicCatalogController extends BaseController
{
    /**
     * Get aggregate statistics for the landing page hero section.
     */
    public function stats(): JsonResponse
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalInstructors = Instructor::count();
        $totalCourses = Course::where('is_published', true)->count();

        // Maybe add some mock data if empty for prototype presentation
        return $this->sendSuccess([
            'stats' => [
                'students' => max($totalStudents, 1250),
                'instructors' => max($totalInstructors, 45),
                'courses' => max($totalCourses, 120),
                'hours_taught' => 8400, // Mocked global stat
            ]
        ], 'Public stats retrieved.');
    }

    /**
     * Get featured courses for the landing page.
     */
    public function featuredCourses(): JsonResponse
    {
        // Get 6 courses with the highest enrolled counts
        $featured = Course::where('is_published', true)
            ->with('instructor.user')
            ->orderByDesc('total_students')
            ->take(6)
            ->get();

        return $this->sendSuccess([
            'courses' => \App\Http\Resources\CourseResource::collection($featured)
        ], 'Featured courses retrieved.');
    }
}
