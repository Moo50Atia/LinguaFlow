<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentComment extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'user_id', 'body'];

    public function moment()
    {
        // The post being commented on.
        return $this->belongsTo(Moment::class);
    }

    public function user()
    {
        // The user who wrote the comment.
        return $this->belongsTo(User::class);
    }
}
