<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomentCorrection extends Model
{
    use HasFactory;

    protected $fillable = ['moment_id', 'user_id', 'original_text', 'corrected_text'];

    public function moment()
    {
        // The original moment post being corrected.
        return $this->belongsTo(Moment::class);
    }

    public function user()
    {
        // The user who provided the language correction.
        return $this->belongsTo(User::class);
    }
}
