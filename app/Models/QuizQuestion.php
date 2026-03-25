<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id', 'course_id', 'type', 'question', 'options',
        'correct_answer', 'order'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function lesson()
    {
        // For lesson-specific quizzes.
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        // For final assessments at the course level.
        return $this->belongsTo(Course::class);
    }
}
