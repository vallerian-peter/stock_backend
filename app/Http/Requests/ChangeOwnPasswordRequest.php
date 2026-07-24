<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangeOwnPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'different:currentPassword',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }
}
