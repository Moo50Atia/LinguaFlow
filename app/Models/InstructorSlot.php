<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorSlot extends Model
{
    use HasFactory;

    protected $fillable = ['instructor_id', 'date', 'time', 'is_booked'];

    protected $casts = [
        'date' => 'date',
        'is_booked' => 'boolean',
    ];

    public function instructor()
    {
        // Each availability slot belongs to one instructor.
        return $this->belongsTo(Instructor::class);
    }

    public function booking()
    {
        // A slot might be linked to exactly one booking if it's already reserved.
        return $this->hasOne(Booking::class);
    }
}
