<?php

namespace App\Repositories\Eloquent\Settings;

use App\Models\Settings\SecuritySetting;
use App\Repositories\Contracts\Settings\SecuritySettingRepositoryInterface;

class SecuritySettingRepository implements SecuritySettingRepositoryInterface
{
    public function findForTenant(?int $schoolId = null): ?SecuritySetting
    {
        return SecuritySetting::withoutGlobalScopes()
            ->when($schoolId === null, fn ($query) => $query->whereNull('school_id'))
            ->when($schoolId !== null, function ($query) use ($schoolId): void {
                $query->where(function ($inner) use ($schoolId): void {
                    $inner->where('school_id', $schoolId)->orWhereNull('school_id');
                })->orderByRaw("case when school_id = {$schoolId} then 0 else 1 end");
            })
            ->first();
    }

    public function upsert(?int $schoolId, array $attributes): SecuritySetting
    {
        SecuritySetting::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $schoolId],
            $attributes
        );

        return $this->findForTenant($schoolId);
    }
}
