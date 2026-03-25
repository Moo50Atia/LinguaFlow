<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'content', 'category', 'images',
        'likes_count', 'comments_count'
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function user()
    {
        // The user who authored the social moment.
        return $this->belongsTo(User::class);
    }

    public function corrections()
    {
        // Community corrections provided for this specific post.
        return $this->hasMany(MomentCorrection::class);
    }

    public function likes()
    {
        // List of users who Liked this specific moment.
        return $this->hasMany(MomentLike::class);
    }

    public function comments()
    {
        // Conversation or feedback left on this moment post.
        return $this->hasMany(MomentComment::class);
    }
}
