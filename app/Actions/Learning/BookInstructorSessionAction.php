<?php

namespace App\Actions\Learning;

use App\Models\Booking;
use App\Models\Instructor;
use App\Models\InstructorSlot;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookInstructorSessionAction
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function execute(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            
            // 1. Prevent users from booking themselves
            $instructor = Instructor::findOrFail($data['instructor_id']);
            if ($instructor->user_id === $user->id) {
                throw ValidationException::withMessages([
                    'instructor_id' => ['You cannot book yourself.']
                ]);
            }

            // 2. Prevent booking a slot that doesn't exist or is already booked
            $slot = InstructorSlot::where('id', $data['instructor_slot_id'])
                ->where('instructor_id', $instructor->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($slot->is_booked) {
                throw ValidationException::withMessages([
                    'instructor_slot_id' => ['This slot has already been booked. Please choose another time.']
                ]);
            }

            // 3. Mark Slot as Booked so no one else can take it concurrently
            $slot->update(['is_booked' => true]);

            // 4. Calculate pricing logic
            $price = $instructor->price_per_hour;
            if ($data['booking_type'] === 'complete-course') {
                // E.g. Multiply by 10 hours for a standard complete private course.
                $price = $price * 10;
            }
            if (($data['course_style'] ?? 'private') === 'group') {
                $price = $price * 0.7; // 30% discount for group sessions
            }

            // 5. Create the pending Booking record
            $booking = Booking::create([
                'user_id'            => $user->id,
                'instructor_id'      => $instructor->id,
                'instructor_slot_id' => $slot->id,
                'type'               => $data['booking_type'],
                'style'              => $data['course_style'] ?? 'private',
                'status'             => 'pending',
                'price'              => $price,
                'notes'              => $data['notes'] ?? null,
            ]);

            // 6. Notify Instructor about the pending booking request
            $this->notificationService->create(
                $instructor->user_id,
                'new_booking',
                "New Booking Request",
                "{$user->name} has requested a {$data['booking_type']} session on " . \Carbon\Carbon::parse($slot->date)->format('M j') . " at {$slot->time}."
            );

            return $booking->load('instructor.user', 'slot');
        });
    }
}
