<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'level'          => $this->level,
            'language'       => $this->language,
            'price'          => $this->price,
            'category'       => $this->category,
            'description'    => $this->description,
            'image'          => $this->image ? url($this->image) : null,
            'is_published'   => clone (bool) $this->is_published,
            'total_lessons'  => $this->lessons_count ?? $this->lessons()->count(),
            'enrolled_count' => $this->enrollments_count ?? $this->enrolled_count,
            'instructor'     => new InstructorResource($this->whenLoaded('instructor')),
            'lessons'        => LessonResource::collection($this->whenLoaded('lessons')),
            'created_at'     => $this->created_at,
        ];
    }
}
