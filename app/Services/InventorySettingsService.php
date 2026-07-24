<?php

namespace App\Services;

use App\Enums\PartStatus;
use App\Models\WorkspaceSetting;

class InventorySettingsService
{
    private ?WorkspaceSetting $settings = null;

    public function get(): WorkspaceSetting
    {
        return $this->settings ??= WorkspaceSetting::query()
            ->firstOrCreate(['key' => 'default'])
            ->refresh();
    }

    public function statusForQuantity(int $quantity): PartStatus
    {
        if ($quantity <= 0) {
            return PartStatus::OUT_OF_STOCK;
        }

        if ($quantity <= $this->get()->lowStockThreshold) {
            return PartStatus::LOW_STOCK;
        }

        return PartStatus::IN_STOCK;
    }
}
