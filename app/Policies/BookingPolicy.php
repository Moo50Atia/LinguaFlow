<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine if the user can interact with the booking.
     */
    public function interact(User $user, Booking $booking): bool
    {
        // Allowed if user is the student who booked it...
        if ($booking->user_id === $user->id) {
            return true;
        }

        // ...OR if the user is the instructor who received it
        if ($user->instructor && $booking->instructor_id === $user->instructor->id) {
            return true;
        }

        // ...OR is an admin
        if ($user->role === 'admin') {
            return true;
        }

        return false;
    }
}
