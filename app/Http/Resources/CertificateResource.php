<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'title'              => $this->title,
            'certificate_number' => $this->certificate_number,
            'level'              => $this->level,
            'category'           => $this->category,
            'issued_at'          => $this->issued_at,
        ];
    }
}
