<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertNotificationResource;
use App\Models\AlertNotification;
use App\Services\AlertNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertNotificationController extends Controller
{
    public function index(
        Request $request,
        AlertNotificationService $service
    ): JsonResponse {
        $service->syncFor($request->user());

        $query = AlertNotification::query()
            ->where('userId', $request->user()->id)
            ->where('active', true)
            ->whereNull('dismissedAt');

        $notifications = (clone $query)
            ->latest()
            ->get();

        return response()->json([
            'data' => AlertNotificationResource::collection($notifications),
            'meta' => [
                'unreadCount' => (clone $query)->whereNull('readAt')->count(),
                'total' => $notifications->count(),
            ],
        ]);
    }

    public function read(Request $request, int $notification): JsonResponse
    {
        $record = $this->findForUser($request, $notification);
        $record->update(['readAt' => $record->readAt ?? now()]);

        return response()->json([
            'data' => new AlertNotificationResource($record->fresh()),
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        AlertNotification::query()
            ->where('userId', $request->user()->id)
            ->where('active', true)
            ->whereNull('dismissedAt')
            ->whereNull('readAt')
            ->update(['readAt' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }

    public function destroy(Request $request, int $notification): JsonResponse
    {
        $record = $this->findForUser($request, $notification);
        $record->update(['dismissedAt' => now()]);

        return response()->json(['message' => 'Notification deleted.']);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        AlertNotification::query()
            ->where('userId', $request->user()->id)
            ->where('active', true)
            ->whereNull('dismissedAt')
            ->update(['dismissedAt' => now()]);

        return response()->json(['message' => 'Notifications deleted.']);
    }

    private function findForUser(Request $request, int $notification): AlertNotification
    {
        return AlertNotification::query()
            ->where('userId', $request->user()->id)
            ->whereKey($notification)
            ->firstOrFail();
    }
}
