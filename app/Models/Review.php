<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'instructor_id', 'rating', 'comment'];

    public function user()
    {
        // The user who wrote the review.
        return $this->belongsTo(User::class);
    }

    public function instructor()
    {
        // The instructor who received the review.
        return $this->belongsTo(Instructor::class);
    }
}
