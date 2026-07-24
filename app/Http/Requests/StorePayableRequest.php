<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePayableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'creditorName' => ['required', 'string', 'max:255'],
            'creditorPhone' => ['nullable', 'string', 'max:50'],
            'referenceNumber' => ['nullable', 'string', 'max:255'],
            'totalAmount' => ['required', 'numeric', 'gt:0'],
            'amountPaid' => ['nullable', 'numeric', 'min:0', 'lte:totalAmount'],
            'debtDate' => ['required', 'date'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:debtDate'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
