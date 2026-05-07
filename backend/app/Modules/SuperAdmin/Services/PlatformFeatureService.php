<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PlatformFeatureService
{
    protected string $cachePrefix = 'platform:features:global';

    public function list(array $filters = []): Collection
    {
        return PlatformSetting::query()
            ->where('setting_group', 'feature_flags')
            ->when(! empty($filters['module']), fn ($query) => $query->where('description', 'like', '%module:'.$filters['module'].'%'))
            ->orderBy('key')
            ->get()
            ->map(fn (PlatformSetting $setting) => $this->mapSettingToFeature($setting));
    }

    public function isEnabled(string $featureCode, ?string $module = null): bool
    {
        $cacheKey = $this->cacheKey($featureCode, $module);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($featureCode, $module): bool {
            $setting = PlatformSetting::query()
                ->where('setting_group', 'feature_flags')
                ->where('key', $this->settingKey($featureCode, $module))
                ->first();

            if (! $setting) {
                return true;
            }

            return filter_var($setting->value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function update(
        string $featureCode,
        array $attributes,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $module = $attributes['module'] ?? $featureCode;
        $enabled = (bool) ($attributes['is_enabled'] ?? true);

        $setting = PlatformSetting::query()->updateOrCreate(
            [
                'setting_group' => 'feature_flags',
                'key' => $this->settingKey($featureCode, $module),
            ],
            [
                'value' => $enabled ? 'true' : 'false',
                'value_type' => 'boolean',
                'is_sensitive' => false,
                'is_public' => false,
                'description' => sprintf('module:%s', $module),
                'metadata' => [
                    'feature_code' => $featureCode,
                    'module' => $module,
                    'label' => $attributes['label'] ?? null,
                    'notes' => $attributes['notes'] ?? null,
                ],
            ]
        );

        Cache::forget($this->cacheKey($featureCode, $module));

        $this->logAction(
            'platform_feature_updated',
            'platform_feature',
            'Global platform feature flag updated.',
            [
                'feature_code' => $featureCode,
                'module' => $module,
                'is_enabled' => $enabled,
            ],
            $performedByUserId,
            $ipAddress,
            $userAgent,
        );

        return $this->mapSettingToFeature($setting);
    }

    protected function cacheKey(string $featureCode, ?string $module = null): string
    {
        return sprintf(
            '%s:%s:%s',
            $this->cachePrefix,
            $module ?? $featureCode,
            $featureCode
        );
    }

    protected function settingKey(string $featureCode, ?string $module = null): string
    {
        return sprintf(
            'feature.%s.%s',
            $module ?? $featureCode,
            $featureCode
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapSettingToFeature(PlatformSetting $setting): array
    {
        $metadata = $setting->metadata ?? [];
        $module = $metadata['module'] ?? $this->extractModuleFromDescription($setting->description);
        $featureCode = $metadata['feature_code'] ?? str($setting->key)->afterLast('.')->toString();

        return [
            'feature_code' => $featureCode,
            'module' => $module ?: $featureCode,
            'is_enabled' => filter_var($setting->value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'label' => $metadata['label'] ?? null,
            'notes' => $metadata['notes'] ?? null,
            'updated_at' => $setting->updated_at?->toISOString(),
        ];
    }

    protected function extractModuleFromDescription(?string $description): ?string
    {
        if (! $description || ! str_starts_with($description, 'module:')) {
            return null;
        }

        return (string) str($description)->after('module:');
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => null,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
