<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutgoingStockRequest;
use App\Http\Resources\OutgoingStock\OutgoingStockResource;
use App\Models\OutgoingStock;
use App\Services\OutgoingStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class OutgoingStockController extends Controller
{
    public function index(Request $request, OutgoingStockService $service): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return OutgoingStockResource::collection($service->paginate($perPage));
    }

    public function store(StoreOutgoingStockRequest $request, OutgoingStockService $service): JsonResponse
    {
        $payload = $service->store($request->validated(), $request->user());

        return (new OutgoingStockResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(OutgoingStock $outgoingStock, OutgoingStockService $service): JsonResponse
    {
        $service->destroy($outgoingStock);

        return response()->json(
            ['message' => 'Outgoing stock record deleted successfully.'],
            Response::HTTP_OK
        );
    }
}
