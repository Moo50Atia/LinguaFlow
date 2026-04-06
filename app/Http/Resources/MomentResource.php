<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MomentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'content'        => $this->content,
            'category'       => $this->category,
            'images'         => $this->images, 
            'likes_count'    => clone (int) $this->likes_count,
            'comments_count' => clone (int) $this->comments_count,
            'is_liked'       => clone (bool) $this->is_liked, // Injected via query
            'user'           => new UserResource($this->whenLoaded('user')),
            'corrections'    => $this->whenLoaded('corrections'), // Basic mapping
            'created_at'     => $this->created_at,
        ];
    }
}
