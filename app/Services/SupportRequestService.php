<?php

namespace App\Services;

use App\Contracts\SupportRequestExporter;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SupportRequestService
{
    public function __construct(
        private readonly SupportRequestExporter $exporter
    ) {}

    public function create(User $user, array $data, string $locale): SupportRequest
    {
        $supportRequest = SupportRequest::query()->create([
            ...$data,
            'referenceNumber' => $this->referenceNumber(),
            'userId' => $user->id,
            'locale' => $locale,
        ]);

        try {
            $this->exporter->export($supportRequest);
        } catch (Throwable $exception) {
            $supportRequest->update([
                'sheetSyncStatus' => 'failed',
                'sheetSyncError' => Str::limit($exception->getMessage(), 1000),
            ]);

            Log::warning('Support request Google Sheets sync failed.', [
                'supportRequestId' => $supportRequest->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return $supportRequest->refresh();
    }

    private function referenceNumber(): string
    {
        do {
            $reference = 'SUP-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (SupportRequest::query()->where('referenceNumber', $reference)->exists());

        return $reference;
    }
}
