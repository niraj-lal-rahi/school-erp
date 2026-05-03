<?php

namespace App\Services\Settings;

use App\Models\Settings\Setting;
use App\Repositories\Contracts\Settings\SettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SettingService
{
    public function __construct(
        protected SettingRepositoryInterface $settings,
        protected SettingAuditService $audits,
    ) {
    }

    public function list(array $filters = []): Collection
    {
        return $this->settings->list($filters)->map(fn (Setting $setting) => $this->sanitizeSetting($setting));
    }

    public function getByKey(string $key, ?int $schoolId = null, mixed $default = null): mixed
    {
        $cacheKey = $this->cacheKey($key, $schoolId);

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($key, $schoolId, $default) {
            $setting = $this->settings->findByKey($key, $schoolId);

            return $setting?->getTypedValue() ?? $default;
        });
    }

    public function findOrFail(int $id): Setting
    {
        return $this->sanitizeSetting($this->settings->findOrFail($id));
    }

    public function findModelOrFail(int $id): Setting
    {
        return $this->settings->findOrFail($id);
    }

    public function update(Setting $setting, array $attributes, $actor = null, ?Request $request = null): Setting
    {
        $oldValue = $setting->getTypedValue();

        if (array_key_exists('value', $attributes)) {
            $attributes['value'] = $this->prepareValueForStorage(
                $attributes['value'],
                $attributes['value_type'] ?? $setting->value_type,
                (bool) ($attributes['is_sensitive'] ?? $setting->is_sensitive)
            );
        }

        $updated = $this->settings->update($setting, $attributes);
        $this->clearCache($updated->key, $updated->school_id);
        $this->audits->log(
            'setting',
            $updated->key,
            $oldValue,
            $updated->getTypedValue(),
            $actor,
            $request,
            $updated->school_id,
            (bool) $updated->is_sensitive
        );

        return $this->sanitizeSetting($updated);
    }

    public function create(array $attributes, $actor = null, ?Request $request = null): Setting
    {
        if (array_key_exists('value', $attributes)) {
            $attributes['value'] = $this->prepareValueForStorage(
                $attributes['value'],
                $attributes['value_type'] ?? 'string',
                (bool) ($attributes['is_sensitive'] ?? false)
            );
        }

        $setting = $this->settings->create($attributes);
        $this->clearCache($setting->key, $setting->school_id);
        $this->audits->log(
            'setting',
            $setting->key,
            null,
            $setting->getTypedValue(),
            $actor,
            $request,
            $setting->school_id,
            (bool) $setting->is_sensitive
        );

        return $this->sanitizeSetting($setting);
    }

    public function clearCache(string $key, ?int $schoolId = null): void
    {
        Cache::forget($this->cacheKey($key, $schoolId));
        Cache::forget($this->cacheKey($key, null));
    }

    public function publicSettings(?int $schoolId = null): Collection
    {
        return $this->settings->allPublic($schoolId)->map(fn (Setting $setting) => $this->sanitizeSetting($setting));
    }

    protected function prepareValueForStorage(mixed $value, string $valueType, bool $isSensitive): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($valueType === 'json' && is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } elseif (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $value = (string) $value;
        }

        if ($valueType === 'encrypted' || $isSensitive) {
            return Crypt::encryptString($value);
        }

        return $value;
    }

    protected function sanitizeSetting(Setting $setting): Setting
    {
        if ($setting->is_sensitive) {
            $setting->setAttribute('value', null);
        } else {
            $setting->setAttribute('value', $setting->getTypedValue());
        }

        return $setting;
    }

    protected function cacheKey(string $key, ?int $schoolId = null): string
    {
        return sprintf('settings.%s.%s', $schoolId ?? 'global', $key);
    }
}
