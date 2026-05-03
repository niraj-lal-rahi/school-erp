<?php

namespace App\Services\Settings;

use App\Models\User;
use App\Repositories\Contracts\Settings\SettingAuditRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SettingAuditService
{
    public function __construct(
        protected SettingAuditRepositoryInterface $audits,
    ) {
    }

    public function list(array $filters = []): Collection
    {
        return $this->audits->list($filters);
    }

    public function log(
        string $settingType,
        ?string $settingKey,
        mixed $oldValue,
        mixed $newValue,
        ?User $actor = null,
        ?Request $request = null,
        ?int $schoolId = null,
        bool $maskSensitive = false,
    ): void {
        $this->audits->create([
            'school_id' => $schoolId,
            'setting_type' => $settingType,
            'setting_key' => $settingKey,
            'old_value' => $this->normalizeAuditValue($oldValue, $maskSensitive),
            'new_value' => $this->normalizeAuditValue($newValue, $maskSensitive),
            'changed_by' => $actor?->id,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    protected function normalizeAuditValue(mixed $value, bool $maskSensitive): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($maskSensitive) {
            return '********';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
