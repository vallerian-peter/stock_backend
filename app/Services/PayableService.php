<?php

namespace App\Services;

use App\Models\Payable;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class PayableService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Payable::query()
            ->with(['creator', 'incomingStock'])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data, User $user): Payable
    {
        $totalAmount = round((float) $data['totalAmount'], 2);
        $amountPaid = min(round((float) ($data['amountPaid'] ?? 0), 2), $totalAmount);
        $balanceAmount = max(0, $totalAmount - $amountPaid);

        return Payable::query()->create([
            'incomingStockId' => $data['incomingStockId'] ?? null,
            'creditorName' => $data['creditorName'],
            'creditorPhone' => $data['creditorPhone'] ?? null,
            'referenceNumber' => $data['referenceNumber'] ?? null,
            'totalAmount' => $totalAmount,
            'amountPaid' => $amountPaid,
            'balanceAmount' => $balanceAmount,
            'status' => $this->statusFor($totalAmount, $amountPaid),
            'debtDate' => $data['debtDate'] ?? now()->toDateString(),
            'dueDate' => $data['dueDate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'createdBy' => $user->id,
        ])->fresh(['creator', 'incomingStock']);
    }

    public function destroy(Payable $payable): void
    {
        $payable->delete();
    }

    private function statusFor(float $totalAmount, float $amountPaid): string
    {
        if ($amountPaid <= 0) {
            return 'PENDING';
        }

        return $amountPaid >= $totalAmount ? 'PAID' : 'PARTIAL';
    }
}
