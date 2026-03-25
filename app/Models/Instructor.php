<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category', 'type', 'price_per_hour', 'bio',
        'specialties', 'schedule', 'years_experience', 'total_students',
        'rating', 'total_reviews'
    ];

    protected $casts = [
        'specialties' => 'array',
        'price_per_hour' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    public function user()
    {
        // The instructor profile is linked to a core User account.
        return $this->belongsTo(User::class);
    }

    public function slots()
    {
        // An instructor has many time slots in their availability calendar.
        return $this->hasMany(InstructorSlot::class);
    }

    public function courses()
    {
        // An instructor can create and offer multiple translation courses.
        return $this->hasMany(Course::class);
    }

    public function bookings()
    {
        // An instructor receives many session bookings from students.
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        // An instructor is reviewed by many students.
        return $this->hasMany(Review::class);
    }

    public function podcasts()
    {
        // An instructor may host several podcast episodes.
        return $this->hasMany(Podcast::class);
    }
}
