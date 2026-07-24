<?php

namespace App\Services;

use App\Contracts\SupportRequestExporter;
use App\Models\SupportRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleSheetsSupportExporter implements SupportRequestExporter
{
    /**
     * @throws ConnectionException
     */
    public function export(SupportRequest $supportRequest): void
    {
        $url = config('services.google_sheets.support_webhook_url');
        $secret = config('services.google_sheets.support_webhook_secret');

        if (! is_string($url) || $url === '') {
            $supportRequest->update([
                'sheetSyncStatus' => 'disabled',
                'sheetSyncError' => null,
            ]);

            return;
        }

        $supportRequest->loadMissing('user');

        $response = Http::asJson()
            ->timeout(8)
            ->retry(2, 150)
            ->post($url, [
                'secret' => $secret,
                'referenceNumber' => $supportRequest->referenceNumber,
                'submittedAt' => $supportRequest->created_at?->toIso8601String(),
                'type' => $supportRequest->type,
                'category' => $supportRequest->category,
                'subject' => $supportRequest->subject,
                'message' => $supportRequest->message,
                'priority' => $supportRequest->priority,
                'contactPreference' => $supportRequest->contactPreference,
                'rating' => $supportRequest->rating,
                'status' => $supportRequest->status,
                'locale' => $supportRequest->locale,
                'sourcePath' => $supportRequest->sourcePath,
                'user' => [
                    'id' => $supportRequest->user?->id,
                    'firstName' => $supportRequest->user?->firstName,
                    'lastName' => $supportRequest->user?->lastName,
                    'email' => $supportRequest->user?->email,
                    'phone' => $supportRequest->user?->phone,
                    'role' => $supportRequest->user?->role?->value,
                ],
            ]);

        if ($response->failed() || $response->json('ok') !== true) {
            throw new RuntimeException(
                "Google Sheets webhook rejected the request (HTTP {$response->status()})."
            );
        }

        $supportRequest->update([
            'sheetSyncStatus' => 'synced',
            'sheetSyncedAt' => now(),
            'sheetSyncError' => null,
        ]);
    }
}
