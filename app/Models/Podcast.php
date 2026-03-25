<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'audio_url', 'cover_image',
        'duration', 'category', 'language', 'level',
        'instructor_id', 'plays_count', 'is_premium'
    ];

    protected $casts = [
        'is_premium' => 'boolean',
    ];

    public function instructor()
    {
        // One podcast can be hosted by one instructor.
        return $this->belongsTo(Instructor::class);
    }
}
