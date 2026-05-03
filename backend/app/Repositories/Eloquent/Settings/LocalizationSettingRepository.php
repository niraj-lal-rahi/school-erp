<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\LocalizationSetting;
use App\Repositories\Contracts\Settings\LocalizationSettingRepositoryInterface;

class LocalizationSettingRepository implements LocalizationSettingRepositoryInterface
{
    public function findByTenant(int $schoolId): ?LocalizationSetting
    {
        return LocalizationSetting::withoutGlobalScopes()->where('school_id', $schoolId)->first();
    }

    public function upsert(int $schoolId, array $attributes): LocalizationSetting
    {
        LocalizationSetting::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $schoolId],
            $attributes
        );

        return $this->findByTenant($schoolId);
    }
}
