<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\LocalizationSetting;

interface LocalizationSettingRepositoryInterface
{
    public function findByTenant(int $schoolId): ?LocalizationSetting;

    public function upsert(int $schoolId, array $attributes): LocalizationSetting;
}
