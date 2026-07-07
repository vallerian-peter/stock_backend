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
            'recipientName' => ['nullable', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'in:SALE,technician,damaged,return,transfer,technician_use,internal_use,damaged_goods,return_to_supplier,branch_transfer,Sale,Sale,sale,TECHNICIAN,DAMAGED,RETURN,TRANSFER'],
            'dispatchedAt' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.partId' => ['required', 'exists:parts,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            
            // Sales fields validated conditionally
            'paymentStatus' => [Rule::requiredIf($isSale), 'string', 'in:PAID,PENDING,PARTIAL,paid,pending,partial'],
            'paymentMethod' => [Rule::requiredIf($isSale), 'string', 'in:CASH,M-PESA,BANK,cash,m-pesa,bank'],
            'amountPaid' => ['nullable', 'numeric', 'min:0'],
            'items.*.unitPrice' => [Rule::requiredIf($isSale), 'nullable', 'numeric', 'min:0'],
        ];
    }
}
