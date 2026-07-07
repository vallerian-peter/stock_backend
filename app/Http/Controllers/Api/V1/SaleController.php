<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSaleRequest;
use App\Http\Resources\Sale\SaleResource;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class SaleController extends Controller
{
    public function index(Request $request, SaleService $service): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return SaleResource::collection($service->paginate($perPage));
    }

    public function store(StoreSaleRequest $request, SaleService $service): JsonResponse
    {
        $payload = $service->store($request->validated(), $request->user());

        return (new SaleResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Sale $sale, SaleService $service): JsonResponse
    {
        $service->destroy($sale);

        return response()->json(
            ['message' => 'Sale record deleted successfully.'],
            Response::HTTP_OK
        );
    }
}
