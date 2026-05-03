<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Controllers\Controller;
use App\Services\Rbac\AccessControlService;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Http\Request;

abstract class BaseSettingController extends Controller
{
    protected function currentSchoolId(Request $request): ?int
    {
        return app(TenantContext::class)->id() ?? $request->user()?->school_id;
    }

    protected function isSuperAdmin(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && app(AccessControlService::class)->isSuperAdmin($user);
    }

    protected function assertTenantAccess(Request $request, ?int $schoolId): void
    {
        if ($this->isSuperAdmin($request)) {
            return;
        }

        abort_unless($schoolId !== null && $schoolId === $this->currentSchoolId($request), 403, 'You are not authorized to access this setting.');
    }

    protected function scopedSchoolId(Request $request, bool $allowGlobal = false): ?int
    {
        if ($allowGlobal && $this->isSuperAdmin($request) && $this->wantsGlobalScope($request)) {
            return null;
        }

        return $this->currentSchoolId($request);
    }

    protected function scopedAttributes(Request $request, array $attributes, bool $allowGlobal = false): array
    {
        if ($allowGlobal && $this->isSuperAdmin($request) && array_key_exists('school_id', $attributes) && $attributes['school_id'] === null) {
            return $attributes;
        }

        $attributes['school_id'] = $this->currentSchoolId($request);

        return $attributes;
    }

    protected function wantsGlobalScope(Request $request): bool
    {
        return $request->query('scope') === 'global' || $request->input('scope') === 'global';
    }
}
