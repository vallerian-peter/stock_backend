<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomingStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoiceNumber' => ['nullable', 'string', 'max:255'],
            'isDebt' => ['sometimes', 'boolean'],
            'supplierName' => ['nullable', 'required_if:isDebt,true', 'string', 'max:255'],
            'supplierPhone' => ['nullable', 'required_if:isDebt,true', 'string', 'max:50'],
            'debtDueDate' => ['nullable', 'date'],
            'amountPaid' => ['nullable', 'numeric', 'min:0'],
            'receivedAt' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.partId' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unitCost' => ['required', 'numeric', 'min:0'],
        ];
    }
}
