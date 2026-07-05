<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SuccessResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'message' => $this['message'],
            'data' => $this['data'] ?? null,
        ];
    }
}
