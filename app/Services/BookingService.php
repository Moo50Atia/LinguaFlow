<?php

namespace App\Services;

use App\Models\Booking;
use App\Services\NotificationService;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function listForUser(int $userId): LengthAwarePaginator
    {
        return Booking::where('user_id', $userId)
            ->with(['instructor.user', 'slot'])
            ->latest()
            ->paginate(15);
    }

    public function cancel(Booking $booking, string $actorRole): void
    {
        if ($booking->status === 'cancelled') {
            return;
        }

        $booking->update(['status' => 'cancelled']);

        if ($booking->slot) {
            $booking->slot->update(['is_booked' => false]);
        }

        // Notify the appropriate party depending on who cancelled
        if ($actorRole === 'student') {
            $this->notificationService->create(
                $booking->instructor->user_id,
                'booking_cancelled',
                'Booking Cancelled',
                "A student has cancelled their session on {$booking->slot->date}."
            );
        } else {
            $this->notificationService->create(
                $booking->user_id,
                'booking_cancelled',
                'Booking Cancelled',
                "The instructor has cancelled your upcoming session on {$booking->slot->date}."
            );
        }
    }

    public function confirm(Booking $booking): void
    {
        if ($booking->status !== 'pending') {
            return;
        }

        $booking->update(['status' => 'confirmed']);

        $this->notificationService->create(
            $booking->user_id,
            'booking_confirmed',
            'Booking Confirmed',
            "Your booking for {$booking->slot->date} has been confirmed by the instructor."
        );
    }
}
