<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePartRequest;
use App\Http\Requests\UpdatePartRequest;
use App\Http\Resources\Part\PartResource;
use App\Models\Part;
use App\Services\PartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PartController extends Controller
{
    public function index(Request $request, PartService $partService): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return PartResource::collection($partService->paginate($perPage));
    }

    public function store(StorePartRequest $request, PartService $partService): JsonResponse
    {
        /** @var UploadedFile|null $image */
        $image = $request->file('image');

        $payload = $partService->store($request->validated(), $image);

        return (new PartResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(
        UpdatePartRequest $request,
        Part $part,
        PartService $partService
    ): JsonResponse {
        /** @var UploadedFile|null $image */
        $image = $request->file('image');

        $payload = $partService->update($part, $request->validated(), $image);

        return (new PartResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(Part $part, PartService $partService): JsonResponse
    {
        $partService->destroy($part);

        return response()->json(
            ['message' => __('parts.delete_success')],
            Response::HTTP_OK
        );
    }
}
