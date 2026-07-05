<?php

namespace App\Http\Resources\Part;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'partName' => $this->partName,
            'partNumber' => $this->partNumber,
            'quantity' => $this->quantity,
            'price' => (string) $this->price,
            'imageUrl' => $this->imageUrl
                ? Storage::disk('public')->url($this->imageUrl)
                : null,
            'imageLastModifiedAt' => $this->imageLastModifiedAt,
            'categoryId' => $this->categoryId,
            'categoryName' => $this->category?->name,
            'status' => $this->status->value,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
