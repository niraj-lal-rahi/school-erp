<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\BrandingSetting;

interface BrandingSettingRepositoryInterface
{
    public function findByTenant(int $schoolId): ?BrandingSetting;

    public function upsert(int $schoolId, array $attributes): BrandingSetting;
}
