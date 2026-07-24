<?php

namespace App\Http\Resources;

use App\Models\UserPreference;
use App\Models\WorkspaceSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var UserPreference $preferences */
        $preferences = $this['preferences'];

        /** @var WorkspaceSetting $workspace */
        $workspace = $this['workspace'];

        return [
            'preferences' => [
                'theme' => $preferences->theme,
                'locale' => $preferences->locale,
                'compactTables' => $preferences->compactTables,
                'lowStockAlerts' => $preferences->lowStockAlerts,
                'salesDigest' => $preferences->salesDigest,
                'debtReminders' => $preferences->debtReminders,
            ],
            'workspace' => [
                'lowStockThreshold' => $workspace->lowStockThreshold,
                'allowNegativeStock' => $workspace->allowNegativeStock,
            ],
            'canManageWorkspace' => $this['canManageWorkspace'],
        ];
    }
}
