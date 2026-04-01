<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'title',
        'level',
        'language',
        'language_flag',
        'total_lessons',
        'price',
        'image',
        'description',
        'category',
        'is_published',
        'enrolled_count'
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function instructor()
    {
        // One course is created by one instructor.
        return $this->belongsTo(Instructor::class);
    }

    public function lessons()
    {
        // A course contains many lessons, ordered sequentially.
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments()
    {
        // Many students can enroll in the same course.
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        // Provides direct access to all students enrolled in this course.
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function quizQuestions()
    {
        // A course can have global questions, for example for final assessments.
        return $this->hasMany(QuizQuestion::class);
    }

    public function certificates()
    {
        // A course issue many certificates to students who complete it.
        return $this->hasMany(Certificate::class);
    }
}
