<?php

namespace App\Queries\Analytics;

use App\Models\Booking;
use App\Models\Enrollment;
use App\Models\StudyDay;

class StudentDashboardQuery
{
    /**
     * Get aggregated data for the student dashboard.
     */
    public function execute(int $userId): array
    {
        // 1. Current Active Enrollments
        $enrollments = Enrollment::where('user_id', $userId)
            ->where('status', 'active')
            ->with(['course.instructor.user'])
            ->get()->map(function ($enrollment) {
                return [
                    'id'            => $enrollment->id,
                    'course_title'  => $enrollment->course->title,
                    'instructor'    => $enrollment->course->instructor->user->name,
                    'progress'      => $enrollment->progress,
                    'image'         => $enrollment->course->image ? url($enrollment->course->image) : null,
                ];
            });

        // 2. Upcoming Booked Sessions (Confirmed specific sessions)
        $upcomingSessions = Booking::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->whereHas('slot', function ($query) {
                $query->whereDate('date', '>=', now()->toDateString());
            })
            ->with(['instructor.user', 'slot'])
            ->orderBy(
                \App\Models\InstructorSlot::select('date')->whereColumn('instructor_slots.id', 'bookings.instructor_slot_id')
            )
            ->take(5)
            ->get()->map(function ($booking) {
                return [
                    'id'               => $booking->id,
                    'instructor'       => $booking->instructor->user->name,
                    'type'             => $booking->type,
                    'date'             => $booking->slot->date->format('Y-m-d'),
                    'time'             => $booking->slot->time,
                ];
            });

        // 3. Activity Heatmap (Last 30 days)
        $thirtyDaysAgo = now()->subDays(30)->toDateString();
        $activityArray = StudyDay::where('user_id', $userId)
            ->whereDate('date', '>=', $thirtyDaysAgo)
            ->pluck('date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        // 4. Quick Counters
        $completedLessons = \App\Models\LessonCompletion::where('user_id', $userId)->count();
        $certificatesCount = \App\Models\Certificate::where('user_id', $userId)->count();

        return [
            'active_enrollments' => $enrollments,
            'upcoming_sessions'  => $upcomingSessions,
            'activity_heatmap'   => $activityArray, // e.g. ["2026-04-01", "2026-04-05", "2026-04-06"]
            'stats'              => [
                'completed_lessons'  => $completedLessons,
                'certificates_count' => $certificatesCount,
            ]
        ];
    }
}
