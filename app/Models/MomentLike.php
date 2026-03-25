<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentLike extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'user_id'];

    public function moment()
    {
        // The post that was liked.
        return $this->belongsTo(Moment::class);
    }

    public function user()
    {
        // The user who liked the post.
        return $this->belongsTo(User::class);
    }
}
