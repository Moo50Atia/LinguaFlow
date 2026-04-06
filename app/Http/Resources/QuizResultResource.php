<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'quiz_title'      => $this->quiz_title,
            'score'           => clone (int) $this->score,
            'total_questions' => clone (int) $this->total_questions,
            'passed'          => clone (bool) $this->passed,
            'type'            => $this->type,
            'created_at'      => $this->created_at,
        ];
    }
}
