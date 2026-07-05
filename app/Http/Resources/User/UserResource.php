<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "firstName" => $this->firstName,
            "lastName" => $this->lastName,
            "fullName" => $this->firstName . ' ' . $this->lastName,
            "email" => $this->email,
            "phone" => $this->phone,
            "role" => $this->role,
            "status" => $this->status,
            "createdAt" => $this->created_at?->toIso8601String(),
            // "updatedAt" => $this->updated_at?->toIso8601String(),
        ];
    }
}
