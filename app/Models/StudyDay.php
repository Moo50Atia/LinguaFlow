<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyDay extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'minutes_studied'];

    protected $casts = [
        'date' => 'date',
    ];

    public function user()
    {
        // High-frequency activity tracking for each user's study days.
        return $this->belongsTo(User::class);
    }
}
