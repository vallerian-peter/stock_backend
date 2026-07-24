<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReceivableRequest;
use App\Http\Resources\Receivable\ReceivableResource;
use App\Models\Receivable;
use App\Services\ReceivableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ReceivableController extends Controller
{
    public function index(Request $request, ReceivableService $service): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return ReceivableResource::collection($service->paginate($perPage));
    }

    public function store(StoreReceivableRequest $request, ReceivableService $service): JsonResponse
    {
        $receivable = $service->store($request->validated(), $request->user());

        return (new ReceivableResource($receivable))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Receivable $receivable, ReceivableService $service): JsonResponse
    {
        $service->destroy($receivable);

        return response()->json(['message' => 'Receivable record deleted successfully.']);
    }
}
