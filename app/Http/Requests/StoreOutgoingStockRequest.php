<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOutgoingStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isSale = strtoupper($this->input('purpose')) === 'SALE';

        return [
            'dispatchNumber' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'in:SALE,DAMAGED,RETURN'],
            'dispatchedAt' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.partId' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            
            // Sales fields validated conditionally
            'saleNumber' => ['nullable', 'string', 'max:255'],
            'isDebt' => ['sometimes', 'boolean'],
            'customerName' => ['nullable', 'required_if:isDebt,true', 'string', 'max:255'],
            'customerPhone' => ['nullable', 'required_if:isDebt,true', 'string', 'max:50'],
            'debtDueDate' => ['nullable', 'date'],
            'paymentStatus' => [Rule::requiredIf($isSale), 'string', 'in:PAID,PENDING,PARTIAL,paid,pending,partial'],
            'paymentMethod' => [Rule::requiredIf($isSale), 'string', 'in:CASH,MOBILE_MONEY,BANK_TRANSFER'],
            'amountPaid' => ['nullable', 'numeric', 'min:0'],
            'additionalAmount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unitPrice' => [Rule::requiredIf($isSale), 'nullable', 'numeric', 'min:0'],
        ];
    }
}
