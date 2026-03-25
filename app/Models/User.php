<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'bio',
        'gender',
        'date_of_birth',
        'location',
        'native_language',
        'cefr_level',
        'role',
        'is_vip',
        'is_online',
        'google_id',
        'last_seen_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_seen_at' => 'datetime',
            'is_vip' => 'boolean',
            'is_online' => 'boolean',
        ];
    }

    /**
     * Relationships
     */

    public function languages()
    {
        // One user can have many native or learning languages.
        return $this->hasMany(UserLanguage::class);
    }

    public function interests()
    {
        // One user can have many interest tags (Travel, Music, etc.).
        return $this->hasMany(UserInterest::class);
    }

    public function instructorProfile()
    {
        // A user might have a separate instructor profile if their role is 'instructor'.
        return $this->hasOne(Instructor::class);
    }

    public function enrollments()
    {
        // A student can enroll in many courses.
        return $this->hasMany(Enrollment::class);
    }

    public function bookings()
    {
        // A student makes many bookings with instructors.
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        // A student can write many reviews for different instructors.
        return $this->hasMany(Review::class);
    }

    public function chats()
    {
        // A user can be part of many direct or group chats.
        return $this->belongsToMany(Chat::class, 'chat_members')
                    ->withPivot('role', 'unread_count')
                    ->withTimestamps();
    }

    public function messages()
    {
        // A user sends many messages across different chats.
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function moments()
    {
        // A user can post many social media style moments.
        return $this->hasMany(Moment::class);
    }

    public function certificates()
    {
        // A student earns certificates upon completing courses.
        return $this->hasMany(Certificate::class);
    }

    public function subscription()
    {
        // A user has one active subscription plan (Pro, VIP, etc.).
        return $this->hasOne(Subscription::class);
    }

    public function studyDays()
    {
        // Tracks each day the user performed study activities.
        return $this->hasMany(StudyDay::class);
    }

    public function notifications()
    {
        // Custom notification system mapped to the notifications table.
        return $this->hasMany(Notification::class);
    }
}
