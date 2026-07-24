<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeOwnPasswordRequest;
use App\Http\Requests\DeleteOwnAccountRequest;
use App\Http\Requests\UpdateOwnProfileRequest;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccountController extends Controller
{
    public function show(Request $request, AccountService $accountService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'canDeleteAccount' => $accountService->canDelete($user),
            ],
        ]);
    }

    public function update(
        UpdateOwnProfileRequest $request,
        AccountService $accountService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $updatedUser = $accountService->updateProfile($user, $request->validated());

        return response()->json([
            'message' => __('account.profile_updated'),
            'data' => [
                'user' => new UserResource($updatedUser),
                'canDeleteAccount' => $accountService->canDelete($updatedUser),
            ],
        ]);
    }

    public function changePassword(
        ChangeOwnPasswordRequest $request,
        AccountService $accountService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $accountService->changePassword($user, $request->validated());

        return response()->json(
            ['message' => __('account.password_updated')],
            Response::HTTP_OK
        );
    }

    public function destroy(
        DeleteOwnAccountRequest $request,
        AccountService $accountService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();
        $accountService->delete($user, $request->validated('currentPassword'));

        return response()->json(
            ['message' => __('account.account_deleted')],
            Response::HTTP_OK
        );
    }
}
