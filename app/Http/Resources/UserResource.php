<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'avatar'          => $this->avatar,
            'bio'             => $this->bio,
            'gender'          => $this->gender,
            'location'        => $this->location,
            'native_language' => $this->native_language,
            'cefr_level'      => $this->cefr_level,
            'role'            => $this->role,
            'is_vip'          => clone (bool) $this->is_vip,
            'is_online'       => clone (bool) $this->is_online,
            // Automatically eager loaded relationships
            'learning_languages' => $this->whenLoaded('learningLanguages'),
            'interests'          => $this->whenLoaded('interests'),
            'created_at'         => $this->created_at,
        ];
    }
}
