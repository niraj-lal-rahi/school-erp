<?php

namespace App\Services\Settings;

use App\Models\Settings\LocalizationSetting;
use App\Repositories\Contracts\Settings\LocalizationSettingRepositoryInterface;
use App\Services\Cache\CacheInvalidationService;
use App\Services\Cache\TenantCacheService;
use Illuminate\Http\Request;

class LocalizationService
{
    public function __construct(
        protected LocalizationSettingRepositoryInterface $localizations,
        protected SettingAuditService $audits,
        protected TenantCacheService $cache,
        protected CacheInvalidationService $invalidator,
    ) {
    }

    public function getForTenant(int $schoolId): ?LocalizationSetting
    {
        return $this->cache->remember(
            'localization',
            $schoolId,
            ['record'],
            now()->addMinutes(30),
            fn () => $this->localizations->findByTenant($schoolId)
        );
    }

    public function update(int $schoolId, array $attributes, $actor = null, ?Request $request = null): LocalizationSetting
    {
        $existing = $this->localizations->findByTenant($schoolId);
        $localization = $this->localizations->upsert($schoolId, $attributes);
        $this->invalidator->localization($schoolId);

        $this->audits->log('localization', 'localization', $existing?->toArray(), $localization->toArray(), $actor, $request, $schoolId);

        return $localization;
    }

    public function tenantConfig(int $schoolId): array
    {
        return $this->cache->remember(
            'localization',
            $schoolId,
            ['tenant-config'],
            now()->addMinutes(30),
            function () use ($schoolId): array {
                $localization = $this->getForTenant($schoolId);

                return [
                    'timezone' => $localization?->timezone ?? 'Asia/Kolkata',
                    'locale' => $localization?->locale ?? 'en',
                    'date_format' => $localization?->date_format ?? 'd-m-Y',
                    'time_format' => $localization?->time_format ?? 'h:i A',
                    'currency' => $localization?->currency ?? 'INR',
                    'currency_symbol' => $localization?->currency_symbol ?? 'Rs',
                    'first_day_of_week' => $localization?->first_day_of_week ?? 'monday',
                ];
            }
        );
    }
}
