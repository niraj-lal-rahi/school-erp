<?php

namespace App\Services\Settings;

class PublicConfigService
{
    public function __construct(
        protected SettingService $settings,
        protected BrandingService $branding,
        protected LocalizationService $localization,
        protected FeatureFlagService $features,
    ) {
    }

    public function get(?int $schoolId = null): array
    {
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
}
