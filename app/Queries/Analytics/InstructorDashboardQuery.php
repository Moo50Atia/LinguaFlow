<?php

namespace App\Queries\Analytics;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class InstructorDashboardQuery
{
    /**
     * Get aggregated data for the instructor dashboard.
     */
    public function execute(int $instructorId): array
    {
        $coursesIds = Course::where('instructor_id', $instructorId)->pluck('id');

        // 1. Total Earnings (Sum of confirmed bookings)
        // More complex logic might involve wallet balances, but this is a simplified metric
        $totalEarnings = Booking::where('instructor_id', $instructorId)
                                ->where('status', 'confirmed') // Or 'completed' if we add that state
                                ->sum('price');

        // 2. Active Students Count
        $activeStudents = Enrollment::whereIn('course_id', $coursesIds)
                                    ->where('status', 'active')
                                    ->distinct('user_id')
                                    ->count('user_id');

        // 3. Recent Enrollments
        $recentEnrollments = Enrollment::whereIn('course_id', $coursesIds)
                                       ->with(['user', 'course'])
                                       ->latest()
                                       ->take(5)
                                       ->get()
                                       ->map(function ($enrollment) {
                                           return [
                                               'student_name' => $enrollment->user->name,
                                               'course_title' => $enrollment->course->title,
                                               'enrolled_at'  => $enrollment->created_at->diffForHumans(),
                                           ];
                                       });

        // 4. Pending Booking Requests (Needing confirmation)
        $pendingBookings = Booking::where('instructor_id', $instructorId)
                                  ->where('status', 'pending')
                                  ->with(['student', 'slot'])
                                  ->get()->map(function ($booking) {
                                      return [
                                          'id'           => $booking->id,
                                          'student_name' => $booking->student->name,
                                          'type'         => $booking->type,
                                          'date'         => $booking->slot ? \Carbon\Carbon::parse($booking->slot->date)->format('Y-m-d') : null,
                                          'time'         => $booking->slot ? $booking->slot->time : null,
                                      ];
                                  });

        // 5. Review aggregation
        $rating = \App\Models\Instructor::where('id', $instructorId)->value('rating') ?? 0.0;

        return [
            'stats' => [
                'total_earnings'  => round($totalEarnings, 2),
                'active_students' => $activeStudents,
                'rating'          => round($rating, 1),
                'total_courses'   => count($coursesIds),
            ],
            'pending_bookings'   => $pendingBookings,
            'recent_enrollments' => $recentEnrollments,
        ];
    }
}
