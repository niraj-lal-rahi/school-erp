<?php

namespace App\Services\Settings;

use App\Services\Cache\TenantCacheService;

class PublicConfigService
{
    public function __construct(
        protected SettingService $settings,
        protected BrandingService $branding,
        protected LocalizationService $localization,
        protected FeatureFlagService $features,
        protected TenantCacheService $cache,
    ) {
    }

    public function get(?int $schoolId = null): array
    {
        return $this->cache->remember(
            'public-config',
            $schoolId,
            [
                'settings-tenant' => $this->cache->namespaceVersion('settings-public', $schoolId),
                'settings-global' => $this->cache->namespaceVersion('settings-public', null),
                'features-tenant' => $this->cache->namespaceVersion('feature-flags', $schoolId),
                'features-global' => $this->cache->namespaceVersion('feature-flags', null),
                'branding' => $schoolId ? $this->cache->namespaceVersion('branding', $schoolId) : 1,
                'localization' => $schoolId ? $this->cache->namespaceVersion('localization', $schoolId) : 1,
            ],
            now()->addMinutes(30),
            function () use ($schoolId): array {
                $publicSettings = $this->settings->publicSettings($schoolId)
                    ->mapWithKeys(fn ($setting) => [$setting->key => $setting->value])
                    ->all();

                $enabledFeatures = $this->features->list(['is_enabled' => true])
                    ->filter(fn ($feature) => $feature->school_id === null || $feature->school_id === $schoolId)
                    ->pluck('feature_code')
                    ->unique()
                    ->values()
                    ->all();

                return [
                    'app_name' => $publicSettings['app_name'] ?? ($this->branding->publicConfig($schoolId ?? 0)['school_name'] ?? 'School ERP'),
                    'branding' => $schoolId ? $this->branding->publicConfig($schoolId) : [],
                    'localization' => $schoolId ? $this->localization->tenantConfig($schoolId) : [],
                    'features' => $enabledFeatures,
                    'settings' => $publicSettings,
                ];
            }
        );
    }
}
