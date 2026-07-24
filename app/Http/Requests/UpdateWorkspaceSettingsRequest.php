<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkspaceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::ADMIN;
    }

    public function rules(): array
    {
        return [
            'lowStockThreshold' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000'],
            'allowNegativeStock' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
