<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    // Your authentication methods will go here
    public function login(LoginRequest $request, AuthService $authService): JsonResponse
    {
        $payload = $authService->login($request->validated());

        return (new LoginResource($payload))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function logout(Request $request, AuthService $authService): JsonResponse
    {
        $authService->logout($request->user());

        return response()->json(
            ['message' => __('auth.logout_success')],
            Response::HTTP_OK
        );
    }
}
