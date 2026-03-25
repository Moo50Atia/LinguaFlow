<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'name', 'type', 'file_path', 'size'];

    public function lesson()
    {
        // Each material resource is linked to one specific lesson.
        return $this->belongsTo(Lesson::class);
    }
}
