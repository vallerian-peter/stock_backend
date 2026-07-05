<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CategoryController extends Controller
{
    public function index(
        Request $request,
        CategoryService $categoryService
    ): AnonymousResourceCollection {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return CategoryResource::collection($categoryService->paginate($perPage));
    }

    public function store(
        StoreCategoryRequest $request,
        CategoryService $categoryService
    ): JsonResponse {
        $payload = $categoryService->store($request->validated());

        return (new CategoryResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        CategoryService $categoryService
    ): JsonResponse {
        $payload = $categoryService->update($category, $request->validated());

        return (new CategoryResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(
        Category $category,
        CategoryService $categoryService
    ): JsonResponse {
        $categoryService->destroy($category);

        return response()->json(
            ['message' => __('categories.delete_success')],
            Response::HTTP_OK
        );
    }
}
