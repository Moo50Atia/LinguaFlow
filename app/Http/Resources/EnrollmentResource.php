<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'course'            => new CourseResource($this->whenLoaded('course')),
            'current_lesson_id' => $this->current_lesson_id,
            'completed_lessons' => $this->completedLessonCount ?? 0, // Injected via query/accessor
            'progress'          => clone (int) $this->progress,
            'status'            => $this->status,
            'enrolled_at'       => $this->created_at,
        ];
    }
}
