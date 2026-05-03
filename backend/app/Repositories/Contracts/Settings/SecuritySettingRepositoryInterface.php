<?php

namespace App\Repositories\Contracts\Settings;

use App\Models\Settings\SecuritySetting;

interface SecuritySettingRepositoryInterface
{
    public function findForTenant(?int $schoolId = null): ?SecuritySetting;

    public function upsert(?int $schoolId, array $attributes): SecuritySetting;
}
