<?php

namespace App\Services;

use App\Models\Receivable;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ReceivableService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Receivable::query()
            ->with(['creator', 'sale'])
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data, User $user): Receivable
    {
        $totalAmount = round((float) $data['totalAmount'], 2);
        $amountPaid = min(round((float) ($data['amountPaid'] ?? 0), 2), $totalAmount);
        $balanceAmount = max(0, $totalAmount - $amountPaid);

        return Receivable::query()->create([
            'saleId' => $data['saleId'] ?? null,
            'customerName' => $data['customerName'],
            'customerPhone' => $data['customerPhone'] ?? null,
            'referenceNumber' => $data['referenceNumber'] ?? null,
            'totalAmount' => $totalAmount,
            'amountPaid' => $amountPaid,
            'balanceAmount' => $balanceAmount,
            'status' => $this->statusFor($totalAmount, $amountPaid),
            'debtDate' => $data['debtDate'] ?? now()->toDateString(),
            'dueDate' => $data['dueDate'] ?? null,
            'notes' => $data['notes'] ?? null,
            'createdBy' => $user->id,
        ])->fresh(['creator', 'sale']);
    }

    public function destroy(Receivable $receivable): void
    {
        $receivable->delete();
    }

    private function statusFor(float $totalAmount, float $amountPaid): string
    {
        if ($amountPaid <= 0) {
            return 'PENDING';
        }

        return $amountPaid >= $totalAmount ? 'PAID' : 'PARTIAL';
    }
}
