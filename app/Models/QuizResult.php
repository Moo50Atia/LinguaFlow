<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'lesson_id', 'course_id', 'quiz_title',
        'course_name', 'score', 'total_questions', 'passed', 'type'
    ];

    protected $casts = [
        'passed' => 'boolean',
    ];

    public function user()
    {
        // The user who took the quiz.
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        // Optional link to a specific lesson quiz.
        return $this->belongsTo(Lesson::class);
    }

    public function course()
    {
        // Optional link to a final course assessment.
        return $this->belongsTo(Course::class);
    }
}
