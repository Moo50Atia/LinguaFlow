<?php

namespace App\Services;

use App\Models\InstructorSlot;

class InstructorSlotService
{
    public function listAvailable(int $instructorId, ?int $month = null, ?int $year = null)
    {
        $query = InstructorSlot::where('instructor_id', $instructorId)
                               ->where('is_booked', false)
                               ->whereDate('date', '>=', now()->toDateString());
        
        if ($month && $year) {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        }

        return $query->orderBy('date')->orderBy('time')->get();
    }

    public function create(int $instructorId, array $data): InstructorSlot
    {
        return InstructorSlot::create([
            'instructor_id' => $instructorId,
            'date'          => $data['date'],
            'time'          => $data['time'],
            'is_booked'     => false,
        ]);
    }

    public function delete(InstructorSlot $slot): void
    {
        if (!$slot->is_booked) {
            $slot->delete();
        } else {
            throw new \Exception("Cannot delete a booked slot.");
        }
    }
}
