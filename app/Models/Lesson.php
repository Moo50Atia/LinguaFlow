<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'order', 'title', 'duration', 'description',
        'notes', 'image', 'has_quiz'
    ];

    protected $casts = [
        'has_quiz' => 'boolean',
    ];

    public function course()
    {
        // Each lesson belongs to exactly one parent course.
        return $this->belongsTo(Course::class);
    }

    public function materials()
    {
        // A lesson can have many downloadable resources (PDF, DOC, MP3).
        return $this->hasMany(LessonMaterial::class);
    }

    public function quizQuestions()
    {
        // A lesson may have several quiz questions for its assessment.
        return $this->hasMany(QuizQuestion::class);
    }

    public function completions()
    {
        // Tracks every time a student (via enrollment) completes this lesson.
        return $this->hasMany(LessonCompletion::class);
    }
}
