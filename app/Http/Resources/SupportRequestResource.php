<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'referenceNumber' => $this->referenceNumber,
            'type' => $this->type,
            'category' => $this->category,
            'subject' => $this->subject,
            'message' => $this->message,
            'priority' => $this->priority,
            'contactPreference' => $this->contactPreference,
            'rating' => $this->rating,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
