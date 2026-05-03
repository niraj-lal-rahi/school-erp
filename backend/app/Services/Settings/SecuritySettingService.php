<?php

namespace App\Services\Settings;

use App\Models\Settings\SecuritySetting;
use App\Repositories\Contracts\Settings\SecuritySettingRepositoryInterface;
use Illuminate\Http\Request;

class SecuritySettingService
{
    public function __construct(
        protected SecuritySettingRepositoryInterface $security,
        protected SettingAuditService $audits,
    ) {
    }

    public function getForTenant(?int $schoolId = null): ?SecuritySetting
    {
        return $this->security->findForTenant($schoolId);
    }

    public function update(?int $schoolId, array $attributes, $actor = null, ?Request $request = null): SecuritySetting
    {
        $existing = $this->getForTenant($schoolId);
        $security = $this->security->upsert($schoolId, $attributes);

        $this->audits->log('security', 'security', $existing?->toArray(), $security->toArray(), $actor, $request, $schoolId);

        return $security;
    }

    public function authRules(?int $schoolId = null): array
    {
        $security = $this->getForTenant($schoolId);

        return [
            'password_min_length' => $security?->password_min_length ?? 8,
            'password_requires_uppercase' => (bool) ($security?->password_requires_uppercase ?? true),
            'password_requires_number' => (bool) ($security?->password_requires_number ?? true),
            'password_requires_symbol' => (bool) ($security?->password_requires_symbol ?? false),
            'session_timeout_minutes' => $security?->session_timeout_minutes ?? 120,
            'max_login_attempts' => $security?->max_login_attempts ?? 5,
            'lockout_minutes' => $security?->lockout_minutes ?? 15,
            'two_factor_enabled' => (bool) ($security?->two_factor_enabled ?? false),
        ];
    }
}
