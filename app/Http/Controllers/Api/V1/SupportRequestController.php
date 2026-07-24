<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupportRequest;
use App\Http\Resources\SupportRequestResource;
use App\Models\SupportRequest;
use App\Models\User;
use App\Services\SupportRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SupportRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $supportRequests = SupportRequest::query()
            ->where('userId', $request->user()->id)
            ->latest()
            ->limit(8)
            ->get();

        return response()->json([
            'data' => SupportRequestResource::collection($supportRequests),
        ]);
    }

    public function store(
        StoreSupportRequest $request,
        SupportRequestService $service
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        $supportRequest = $service->create(
            $user,
            $request->validated(),
            app()->getLocale()
        );

        return (new SupportRequestResource($supportRequest))
            ->additional(['message' => __('support.submitted')])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
