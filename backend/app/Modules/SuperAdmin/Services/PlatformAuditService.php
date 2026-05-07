<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlatformAuditService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return PlatformAuditLog::query()
            ->with(['tenant:id,name,code,slug', 'user:id,first_name,last_name,name,email'])
            ->when(isset($filters['tenant_id']) && $filters['tenant_id'] !== null, fn (Builder $query) => $query->where('tenant_id', (int) $filters['tenant_id']))
            ->when(isset($filters['user_id']) && $filters['user_id'] !== null, fn (Builder $query) => $query->where('user_id', (int) $filters['user_id']))
            ->when(! empty($filters['action']), fn (Builder $query) => $query->where('action', (string) $filters['action']))
            ->when(! empty($filters['module']), fn (Builder $query) => $query->where('module', (string) $filters['module']))
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->where('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->where('created_at', '<=', $filters['date_to']))
            ->when(! empty($filters['search']), function (Builder $query) use ($filters): void {
                $search = trim((string) $filters['search']);

                $query->where(function (Builder $builder) use ($search): void {
                    $builder->where('action', 'like', '%'.$search.'%')
                        ->orWhere('module', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('ip_address', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        string $action,
        string $module,
        string $description,
        ?int $tenantId = null,
        ?int $userId = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): PlatformAuditLog {
        return PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $this->sanitizeMetadata($metadata),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function tenantCreated(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_created', 'platform_tenant', 'Tenant created.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function tenantSuspended(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_suspended', 'platform_tenant', 'Tenant suspended.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function databaseProvisioned(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_db_provisioned', 'tenant_database', 'Tenant database provisioned.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function databaseCredentialsRotated(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_db_credentials_rotated', 'tenant_database', 'Tenant database credentials rotated.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function planChanged(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_plan_changed', 'tenant_subscription', 'Tenant subscription plan changed.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function paymentMarkedPaid(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('billing_marked_paid', 'tenant_billing', 'Tenant billing marked paid.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function featureToggled(?int $tenantId = null, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('feature_toggled', $tenantId ? 'tenant_feature' : 'platform_feature', 'Feature access updated.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function impersonationStarted(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('impersonation_started', 'tenant_impersonation', 'Impersonation started.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function impersonationStopped(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('impersonation_stopped', 'tenant_impersonation', 'Impersonation stopped.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function emergencyAccessRequested(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('emergency_access_requested', 'emergency_access', 'Emergency access requested.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function emergencyAccessApproved(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('emergency_access_approved', 'emergency_access', 'Emergency access approved.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function platformSettingChanged(?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('platform_setting_changed', 'platform_settings', 'Platform setting changed.', null, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function databaseConnectionFailed(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_db_connection_failed', 'tenant_database', 'Tenant database connection failed.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function backupRestored(int $tenantId, ?int $userId = null, array $metadata = [], ?string $ipAddress = null, ?string $userAgent = null): PlatformAuditLog
    {
        return $this->record('tenant_backup_restored', 'tenant_backup', 'Tenant backup restored.', $tenantId, $userId, $metadata, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    protected function sanitizeMetadata(array $metadata): array
    {
        $sensitiveKeys = [
            'password',
            'database_password',
            'database_username',
            'database_host',
            'database_port',
            'token',
            'secret',
            'key',
            'private_key',
            'api_key',
            'authorization',
        ];

        foreach ($metadata as $key => $value) {
            if (is_array($value)) {
                $metadata[$key] = $this->sanitizeMetadata($value);
                continue;
            }

            if (in_array(strtolower((string) $key), $sensitiveKeys, true)) {
                $metadata[$key] = '***';
            }
        }

        return $metadata;
    }
}
