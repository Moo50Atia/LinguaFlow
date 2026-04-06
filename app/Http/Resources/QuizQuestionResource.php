<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'question'       => $this->question,
            'options'        => $this->options, // is array
            'correct_answer' => clone (int) $this->correct_answer,
            'order'          => clone (int) $this->order,
            // Only if you want to include nested objects:
            // 'lesson_id'   => $this->lesson_id,
            // 'course_id'   => $this->course_id,
        ];
    }
}
