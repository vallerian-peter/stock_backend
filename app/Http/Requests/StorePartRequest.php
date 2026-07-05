<?php

namespace App\Http\Requests;

use App\Enums\PartStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'partName' => ['required', 'string', 'max:255'],
            'partNumber' => ['required', 'string', 'max:255', Rule::unique('parts', 'partNumber')],
            'quantity' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'imageLastModifiedAt' => ['nullable', 'integer', 'min:1'],
            'categoryId' => ['nullable', 'exists:categories,id'],
            'status' => ['required', Rule::enum(PartStatus::class)],
        ];
    }
}
