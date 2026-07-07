<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIncomingStockRequest;
use App\Http\Resources\IncomingStock\IncomingStockResource;
use App\Models\IncomingStock;
use App\Services\IncomingStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class IncomingStockController extends Controller
{
    public function index(Request $request, IncomingStockService $service): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return IncomingStockResource::collection($service->paginate($perPage));
    }

    public function store(StoreIncomingStockRequest $request, IncomingStockService $service): JsonResponse
    {
        $payload = $service->store($request->validated(), $request->user());

        return (new IncomingStockResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(IncomingStock $incomingStock, IncomingStockService $service): JsonResponse
    {
        $service->destroy($incomingStock);

        return response()->json(
            ['message' => 'Incoming stock record deleted successfully.'],
            Response::HTTP_OK
        );
    }
}
