<?php

namespace App\Http\Requests;

use App\Enums\PartStatus;
use App\Models\Part;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Part $part */
        $part = $this->route('part');

        return [
            'partName' => ['sometimes', 'required', 'string', 'max:255'],
            'partNumber' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('parts', 'partNumber')->ignore($part),
            ],
            'quantity' => ['sometimes', 'required', 'integer', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'imageLastModifiedAt' => ['nullable', 'integer', 'min:1'],
            'categoryId' => ['nullable', 'exists:categories,id'],
            'status' => ['sometimes', 'required', Rule::enum(PartStatus::class)],
        ];
    }
}
