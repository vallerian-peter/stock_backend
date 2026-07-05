<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserController extends Controller
{
    public function index(Request $request, UserService $userService): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return UserResource::collection($userService->paginate($perPage));
    }

    public function store(RegisterRequest $request, UserService $userService): JsonResponse
    {
        $payload = $userService->store($request->validated());

        return (new UserResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        UserService $userService
    ): JsonResponse {
        $payload = $userService->update($user, $request->validated());

        return (new UserResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(User $user, UserService $userService): JsonResponse
    {
        $userService->destroy($user);

        return response()->json(
            ['message' => __('users.delete_success')],
            Response::HTTP_OK
        );
    }
}
