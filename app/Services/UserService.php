<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->latest()
            ->paginate($perPage);
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $data['password'] = Hash::make($data['password']);
            $user = User::query()->create($data);

            // SMS sent here later..

            return $user->fresh();
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = [
                'firstName' => $data['firstName'] ?? $user->firstName,
                'lastName' => $data['lastName'] ?? $user->lastName,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? $user->phone,
                'role' => $data['role'] ?? $user->role,
                'status' => $data['status'] ?? $user->status,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            return $user->fresh();
        });
    }

    public function destroy(User $user): void
    {
        if ($user->role === UserRole::ADMIN) {
            throw ValidationException::withMessages([
                'user' => [__('users.admin_delete_forbidden')],
            ]);
        }

        DB::transaction(function () use ($user): void {
            $user->delete();
        });
    }
}
