<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'type'           => $this->type,
            'style'          => $this->style,
            'status'         => $this->status,
            'price'          => clone (float) $this->price,
            'notes'          => $this->notes,
            'instructor'     => new InstructorResource($this->whenLoaded('instructor')),
            'student'        => new UserResource($this->whenLoaded('student')),
            'slot_date'      => $this->whenLoaded('slot', fn() => $this->slot->date->format('Y-m-d')),
            'slot_time'      => $this->whenLoaded('slot', fn() => $this->slot->time),
            'created_at'     => $this->created_at,
        ];
    }
}
