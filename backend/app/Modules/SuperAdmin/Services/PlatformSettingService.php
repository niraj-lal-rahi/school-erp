<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PlatformSettingService
{
    protected string $platformConnection = 'platform';

    /**
     * @var list<string>
     */
    protected array $allowedGroups = [
        'email',
        'sms',
        'payment',
        'storage',
        'security',
        'branding',
        'localization',
    ];

    public function list(?string $group = null): Collection
    {
        if ($group !== null) {
            return $this->getGroup($group);
        }

        return collect($this->allowedGroups)->mapWithKeys(fn (string $settingGroup): array => [
            $settingGroup => $this->getGroup($settingGroup),
        ]);
    }

    public function getGroup(string $group): Collection
    {
        $this->assertAllowedGroup($group);

        return Cache::remember(
            $this->cacheKey($group),
            now()->addMinutes(30),
            fn () => PlatformSetting::query()
                ->where('setting_group', $group)
                ->orderBy('key')
                ->get()
        );
    }

    /**
     * @param  list<array<string, mixed>>  $settings
     */
    public function updateGroup(
        string $group,
        array $settings,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Collection {
        $this->assertAllowedGroup($group);

        return DB::connection($this->platformConnection)->transaction(function () use ($group, $settings, $performedByUserId, $ipAddress, $userAgent): Collection {
            $updated = collect();

            foreach ($settings as $setting) {
                $isSensitive = (bool) ($setting['is_sensitive'] ?? false);
                $valueType = $setting['value_type'] ?? ($isSensitive ? 'encrypted' : 'string');
                $storedValue = $this->prepareStoredValue($setting['value'] ?? null, $valueType, $isSensitive);

                $record = PlatformSetting::query()->updateOrCreate(
                    [
                        'setting_group' => $group,
                        'key' => $setting['key'],
                    ],
                    [
                        'value' => $storedValue,
                        'value_type' => $valueType,
                        'is_sensitive' => $isSensitive,
                        'is_public' => (bool) ($setting['is_public'] ?? false),
                        'description' => $setting['description'] ?? null,
                        'metadata' => $setting['metadata'] ?? null,
                    ]
                );

                $updated->push($record);
            }

            Cache::forget($this->cacheKey($group));

            $this->logAction(
                'platform_settings_updated',
                'platform_settings',
                'Platform settings group updated.',
                [
                    'group' => $group,
                    'keys' => collect($settings)->pluck('key')->values()->all(),
                ],
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            return $this->getGroup($group);
        });
    }

    protected function prepareStoredValue(mixed $value, string $valueType, bool $isSensitive): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = match ($valueType) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL) ? 'true' : 'false',
            'integer' => (string) ((int) $value),
            'json', 'file', 'encrypted' => is_string($value) ? $value : json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };

        if (! $isSensitive) {
            return $normalized;
        }

        return Crypt::encryptString($normalized);
    }

    protected function assertAllowedGroup(string $group): void
    {
        if (! in_array($group, $this->allowedGroups, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported platform settings group [%s].', $group));
        }
    }

    protected function cacheKey(string $group): string
    {
        return sprintf('platform:settings:%s', $group);
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
