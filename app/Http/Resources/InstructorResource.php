<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user'             => new UserResource($this->whenLoaded('user')),
            'category'         => $this->category,
            'type'             => $this->type,
            'price_per_hour'   => $this->price_per_hour,
            'bio'              => $this->bio,
            'specialties'      => $this->specialties, // Already cast to array via model if set up
            'rating'           => $this->rating,
            'total_reviews'    => $this->reviews()->count(), 
            'total_students'   => $this->enrollments()->count(), // Assumes basic model helper mapping 
            'years_experience' => $this->years_experience,
            'courses'          => CourseResource::collection($this->whenLoaded('courses')),
        ];
    }
}
