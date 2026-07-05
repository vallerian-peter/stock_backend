<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function index(Request $request, UserService $userService): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return UserResource::collection($userService->paginate($perPage));
    }

    public function store(StoreUserRequest $request, UserService $userService): JsonResponse
    {
        $user = $userService->store($request->validated());

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UserService $userService
    ): UserResource {
        return new UserResource($userService->update($user, $request->validated()));
    }

    public function destroy(User $user, UserService $userService): JsonResponse
    {
        $userService->destroy($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ], Response::HTTP_OK);
    }
}
