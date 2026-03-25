<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'current_lesson', 'completed_lessons',
        'progress', 'status'
    ];

    protected $casts = [
        'progress' => 'decimal:2',
    ];

    public function user()
    {
        // The student who enrolled in the course.
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        // The course the student is currently learning.
        return $this->belongsTo(Course::class);
    }

    public function lessonCompletions()
    {
        // Records for each lesson the student has finished in this course.
        return $this->hasMany(LessonCompletion::class);
    }
}
