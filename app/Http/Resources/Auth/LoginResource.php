<?php

namespace App\Http\Resources\Auth;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'message' => __('auth.login_success'),
            'token' => $this['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($this['user']),
        ];
    }
}
