<?php

namespace App\Services;

use App\Events\User\UserCreated;
use App\Events\User\UserDeleted;
use App\Events\User\UserUpdated;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => $data['role'],
                'status' => $data['status'],
                'password' => Hash::make($data['password']),
            ]);

            event(new UserCreated($user));

            return $user->fresh();
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $payload = [
                'first_name' => $data['first_name'] ?? $user->first_name,
                'last_name' => $data['last_name'] ?? $user->last_name,
                'email' => $data['email'] ?? $user->email,
                'phone' => array_key_exists('phone', $data) ? $data['phone'] : $user->phone,
                'role' => $data['role'] ?? $user->role,
                'status' => $data['status'] ?? $user->status,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);

            $freshUser = $user->fresh();

            event(new UserUpdated($freshUser));

            return $freshUser;
        });
    }

    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $snapshot = [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'phone' => $user->phone,
            ];

            $user->delete();

            event(new UserDeleted($snapshot));
        });
    }
}
