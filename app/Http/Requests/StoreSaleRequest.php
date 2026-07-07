<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'saleNumber' => ['nullable', 'string', 'max:255'],
            'customerName' => ['nullable', 'string', 'max:255'],
            'paymentStatus' => ['required', 'string', 'in:PAID,PENDING,PARTIAL,paid,pending,partial'],
            'paymentMethod' => ['required', 'string', 'in:CASH,M-PESA,BANK,cash,m-pesa,bank'],
            'amountPaid' => ['nullable', 'numeric', 'min:0'],
            'soldAt' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.partId' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
        ];
    }
}
