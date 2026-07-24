<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Part;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    public function __construct(
        private readonly InventorySettingsService $inventorySettings
    ) {}

    public function get(User $user): array
    {
        return [
            'preferences' => $this->preferences($user),
            'workspace' => $this->inventorySettings->get(),
            'canManageWorkspace' => $user->role === UserRole::ADMIN,
        ];
    }

    public function updatePreferences(User $user, array $data): array
    {
        DB::transaction(function () use ($user, $data): void {
            $this->preferences($user)->update($data);
        });

        return $this->get($user);
    }

    public function updateWorkspace(User $user, array $data): array
    {
        DB::transaction(function () use ($data): void {
            $this->inventorySettings->get()->update($data);

            if (array_key_exists('lowStockThreshold', $data)) {
                Part::query()->eachById(function (Part $part): void {
                    $part->update([
                        'status' => $this->inventorySettings
                            ->statusForQuantity($part->quantity),
                    ]);
                });
            }
        });

        return $this->get($user);
    }

    private function preferences(User $user): UserPreference
    {
        return UserPreference::query()->firstOrCreate([
            'userId' => $user->id,
        ])->refresh();
    }
}
