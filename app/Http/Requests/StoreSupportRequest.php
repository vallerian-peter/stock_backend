<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['help', 'chat', 'bug', 'feedback'])],
            'category' => ['required', 'string', 'max:80'],
            'subject' => ['required', 'string', 'min:5', 'max:120'],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'contactPreference' => ['required', Rule::in(['email', 'phone'])],
            'rating' => [
                'nullable',
                'required_if:type,feedback',
                'integer',
                'between:1,5',
            ],
            'sourcePath' => ['nullable', 'string', 'max:255'],
        ];
    }
}
