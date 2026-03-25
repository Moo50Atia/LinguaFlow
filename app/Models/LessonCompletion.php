<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id', 'lesson_id', 'score', 'passed', 'completed_at'
    ];

    protected $casts = [
        'passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function enrollment()
    {
        // Links back to the student's specific enrollment in that course.
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson()
    {
        // The specific lesson that was completed.
        return $this->belongsTo(Lesson::class);
    }
}
