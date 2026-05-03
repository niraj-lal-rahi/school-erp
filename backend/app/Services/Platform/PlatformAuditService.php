<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformAdmin;
use App\Models\Platform\PlatformAuditLog;
use App\Models\Platform\PlatformTenant;
use App\Models\Saas\Tenant as SaasTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PlatformAuditService
{
    public function log(
        string $action,
        ?string $module = null,
        ?string $description = null,
        PlatformTenant|Model|int|null $tenant = null,
        ?User $actor = null,
        array $metadata = [],
        ?Request $request = null,
    ): PlatformAuditLog {
        return PlatformAuditLog::query()->create([
            'tenant_id' => $this->resolvePlatformTenantId($tenant),
            'user_id' => $actor?->id ?? $request?->user()?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $this->maskSensitive(array_filter($metadata, fn ($value) => $value !== null)),
        ]);
    }

    public function logTenantCreated(PlatformTenant|Model|int|null $tenant, ?User $actor = null, array $metadata = [], ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'tenant.created',
            'tenancy',
            'Tenant created in platform context.',
            $tenant,
            $actor,
            $metadata,
            $request,
        );
    }

    public function logTenantStatusChanged(string $status, PlatformTenant|Model|int|null $tenant, ?User $actor = null, array $metadata = [], ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'tenant.'.$status,
            'tenancy',
            sprintf('Tenant marked %s.', $status),
            $tenant,
            $actor,
            $metadata,
            $request,
        );
    }

    public function logTenantDatabaseCreated(PlatformTenant $tenant, array $metadata = [], ?User $actor = null, ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'tenant.database.created',
            'database',
            'Tenant database provisioned.',
            $tenant,
            $actor,
            $metadata,
            $request,
        );
    }

    public function logTenantDatabaseConnectionTested(PlatformTenant $tenant, bool $success, array $metadata = [], ?User $actor = null, ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'tenant.database.connection_tested',
            'database',
            $success ? 'Tenant database connection test succeeded.' : 'Tenant database connection test failed.',
            $tenant,
            $actor,
            array_merge($metadata, ['success' => $success]),
            $request,
        );
    }

    public function logSuperAdminLogin(User $user, array $metadata = [], ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'platform_admin.login',
            'auth',
            'Platform administrator login recorded.',
            $this->resolvePlatformTenantForUser($user),
            $user,
            array_merge($metadata, [
                'platform_admin' => $this->isPlatformAdmin($user),
                'super_admin' => $this->isSuperAdmin($user),
            ]),
            $request,
        );
    }

    public function logSuperAdminImpersonation(User $user, PlatformTenant|Model|int|null $tenant = null, array $metadata = [], ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'platform_admin.impersonation',
            'security',
            'Platform administrator impersonation event recorded.',
            $tenant,
            $user,
            $metadata,
            $request,
        );
    }

    public function logEmergencyAccessToggled(PlatformTenant|Model|int|null $tenant, bool $enabled, ?User $actor = null, array $metadata = [], ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            $enabled ? 'tenant.security.emergency_access_enabled' : 'tenant.security.emergency_access_disabled',
            'security',
            $enabled ? 'Emergency access enabled for tenant.' : 'Emergency access disabled for tenant.',
            $tenant,
            $actor,
            array_merge($metadata, ['enabled' => $enabled]),
            $request,
        );
    }

    public function logDatabaseConnectionFailure(PlatformTenant|Model|int|null $tenant, array $metadata = [], ?User $actor = null, ?Request $request = null): PlatformAuditLog
    {
        return $this->log(
            'tenant.db_connection_failed',
            'database',
            'Tenant database connection failed.',
            $tenant,
            $actor,
            $metadata,
            $request,
        );
    }

    protected function resolvePlatformTenantId(PlatformTenant|Model|int|null $tenant): ?int
    {
        if ($tenant instanceof PlatformTenant) {
            return $tenant->id;
        }

        if (is_int($tenant)) {
            return PlatformTenant::query()->whereKey($tenant)->value('id');
        }

        if ($tenant instanceof SaasTenant) {
            return PlatformTenant::query()
                ->where('id', $tenant->id)
                ->orWhere('code', $tenant->code)
                ->orWhere('slug', $tenant->slug)
                ->value('id');
        }

        if ($tenant instanceof Model && isset($tenant->id)) {
            return PlatformTenant::query()->whereKey((int) $tenant->id)->value('id');
        }

        return null;
    }

    protected function resolvePlatformTenantForUser(User $user): ?PlatformTenant
    {
        return PlatformTenant::query()
            ->where('id', $user->school_id)
            ->orWhere('code', $user->school?->code)
            ->orWhere('slug', $user->school?->slug)
            ->first();
    }

    protected function isPlatformAdmin(User $user): bool
    {
        return PlatformAdmin::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    protected function isSuperAdmin(User $user): bool
    {
        return $user->roles()
            ->where(function ($query): void {
                $query->where('roles.code', 'super_admin')
                    ->orWhere('roles.slug', 'super_admin');
            })
            ->exists();
    }

    protected function maskSensitive(array $metadata): array
    {
        $masked = [];

        foreach ($metadata as $key => $value) {
            $normalized = strtolower((string) $key);

            if (str_contains($normalized, 'password')
                || str_contains($normalized, 'secret')
                || str_contains($normalized, 'token')
                || str_contains($normalized, 'signature')
                || str_contains($normalized, 'authorization')
                || str_contains($normalized, 'database_host')
                || str_contains($normalized, 'database_port')
                || str_contains($normalized, 'database_username')
                || str_contains($normalized, 'database_password')
            ) {
                $masked[$key] = '***masked***';
                continue;
            }

            $masked[$key] = is_array($value) ? $this->maskSensitive($value) : $value;
        }

        return $masked;
    }
}
