<?php

namespace App\Queries\Instructor;

use App\Models\InstructorSlot;

class InstructorSlotsQuery
{
    /**
     * Get instructor slots grouped by date for frontend calendar display.
     *
     * @param int $instructorId
     * @param int|null $month
     * @param int|null $year
     * @return array
     */
    public function execute(int $instructorId, ?int $month = null, ?int $year = null): array
    {
        $query = InstructorSlot::where('instructor_id', $instructorId)
                               ->where('is_booked', false)
                               ->whereDate('date', '>=', now()->toDateString());
        
        if ($month && $year) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        }

        $slots = $query->orderBy('date')->orderBy('time')->get();

        // Group by Date for frontend consumption
        $grouped = [];
        foreach ($slots as $slot) {
            $date = $slot->date->format('Y-m-d');
            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $slot;
        }

        return $grouped;
    }
}
