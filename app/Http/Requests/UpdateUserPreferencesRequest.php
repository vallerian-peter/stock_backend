<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'theme' => ['sometimes', 'required', Rule::in(['light', 'dark', 'system'])],
            'locale' => ['sometimes', 'required', Rule::in(['en', 'sw'])],
            'compactTables' => ['sometimes', 'required', 'boolean'],
            'lowStockAlerts' => ['sometimes', 'required', 'boolean'],
            'salesDigest' => ['sometimes', 'required', 'boolean'],
            'debtReminders' => ['sometimes', 'required', 'boolean'],
        ];
    }
}
