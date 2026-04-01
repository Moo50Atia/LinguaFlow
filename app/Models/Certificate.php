<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'title',
        'certificate_number',
        'level',
        'category',
        'file_path',
        'issued_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    public function user()
    {
        // Each certificate is awarded to one specific student.
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        // A certificate is linked to the course it was earned from.
        return $this->belongsTo(Course::class);
    }
}
