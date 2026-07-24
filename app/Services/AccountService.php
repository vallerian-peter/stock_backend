<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function updateProfile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->update($data);

            return $user->fresh();
        });
    }

    public function changePassword(User $user, array $data): void
    {
        if (! Hash::check($data['currentPassword'], $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => [__('account.current_password_invalid')],
            ]);
        }

        DB::transaction(function () use ($user, $data): void {
            $user->update(['password' => $data['password']]);
            $user->tokens()->delete();
        });
    }

    public function canDelete(User $user): bool
    {
        if ($user->role !== UserRole::ADMIN) {
            return true;
        }

        return User::query()
            ->where('role', UserRole::ADMIN->value)
            ->count() > 1;
    }

    public function delete(User $user, string $currentPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => [__('account.current_password_invalid')],
            ]);
        }

        DB::transaction(function () use ($user): void {
            if ($user->role === UserRole::ADMIN) {
                $adminCount = User::query()
                    ->where('role', UserRole::ADMIN->value)
                    ->lockForUpdate()
                    ->get(['id'])
                    ->count();

                if ($adminCount <= 1) {
                    throw ValidationException::withMessages([
                        'account' => [__('account.last_admin_delete_forbidden')],
                    ]);
                }
            }

            $user->tokens()->delete();
            $user->delete();
        });
    }
}
