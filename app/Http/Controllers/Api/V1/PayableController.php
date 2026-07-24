<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayableRequest;
use App\Http\Resources\Payable\PayableResource;
use App\Models\Payable;
use App\Services\PayableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PayableController extends Controller
{
    public function index(Request $request, PayableService $service): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return PayableResource::collection($service->paginate($perPage));
    }

    public function store(StorePayableRequest $request, PayableService $service): JsonResponse
    {
        $payable = $service->store($request->validated(), $request->user());

        return (new PayableResource($payable))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Payable $payable, PayableService $service): JsonResponse
    {
        $service->destroy($payable);

        return response()->json(['message' => 'Payable record deleted successfully.']);
    }
}
