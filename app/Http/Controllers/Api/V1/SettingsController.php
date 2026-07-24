<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserPreferencesRequest;
use App\Http\Requests\UpdateWorkspaceSettingsRequest;
use App\Http\Resources\SettingsResource;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingsController extends Controller
{
    public function show(Request $request, SettingsService $settingsService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return (new SettingsResource($settingsService->get($user)))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function updatePreferences(
        UpdateUserPreferencesRequest $request,
        SettingsService $settingsService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return (new SettingsResource(
            $settingsService->updatePreferences($user, $request->validated())
        ))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function updateWorkspace(
        UpdateWorkspaceSettingsRequest $request,
        SettingsService $settingsService
    ): JsonResponse {
        /** @var User $user */
        $user = $request->user();

        return (new SettingsResource(
            $settingsService->updateWorkspace($user, $request->validated())
        ))
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
