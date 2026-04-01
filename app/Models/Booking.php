<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'instructor_id',
        'instructor_slot_id',
        'booking_type',
        'course_style',
        'date',
        'time',
        'price',
        'status',
        'notes'
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        // The student who made the booking.
        return $this->belongsTo(User::class);
    }

    public function instructor()
    {
        // The instructor who is being booked.
        return $this->belongsTo(Instructor::class);
    }

    public function slot()
    {
        // The specific time slot reserved for this session.
        return $this->belongsTo(InstructorSlot::class, 'instructor_slot_id');
    }
}
