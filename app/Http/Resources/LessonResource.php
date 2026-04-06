<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'order'           => $this->order,
            'description'     => $this->description,
            'notes'           => $this->notes,
            'duration'        => $this->duration,
            'image'           => $this->image ? url($this->image) : null,
            'status'          => $this->status,
            'has_quiz'        => $this->quizzes()->exists(),
            'materials_count' => $this->materials()->count(),
            // When explicitly loading materials:
            // 'materials' => MaterialResource::collection($this->whenLoaded('materials')),
        ];
    }
}
