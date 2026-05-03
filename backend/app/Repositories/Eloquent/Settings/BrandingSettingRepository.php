<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\BrandingSetting;
use App\Repositories\Contracts\Settings\BrandingSettingRepositoryInterface;

class BrandingSettingRepository implements BrandingSettingRepositoryInterface
{
    public function findByTenant(int $schoolId): ?BrandingSetting
    {
        return BrandingSetting::withoutGlobalScopes()->where('school_id', $schoolId)->first();
    }

    public function upsert(int $schoolId, array $attributes): BrandingSetting
    {
        BrandingSetting::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $schoolId],
            $attributes
        );

        return $this->findByTenant($schoolId);
    }
}
