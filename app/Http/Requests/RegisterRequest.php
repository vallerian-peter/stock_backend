<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstName'     => ['required', 'string', 'max:255'],
            'lastName'      => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'phone'         => ['required', 'string', 'max:20'],
            'role'          => ['required', Rule::enum(UserRole::class)],
            'status'        => ['required', Rule::enum(UserStatus::class)],
        ];
    }
}
