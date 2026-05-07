<?php

namespace App\Modules\SuperAdmin\Services;

use App\Models\User;
use App\Modules\SuperAdmin\Models\EmergencyAccessLog;
use App\Modules\SuperAdmin\Models\PlatformAdmin;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use RuntimeException;

class EmergencyAccessService
{
    public function requestAccess(
        PlatformTenant $tenant,
        User $requester,
        string $reason,
        ?int $ttlMinutes = null,
        bool $requiresSecondAdminApproval = true,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): EmergencyAccessLog {
        $this->assertPlatformAdmin($requester);
        $this->assertTenantAllowsEmergencyAccess($tenant);

        $ttlMinutes ??= (int) config('erp.jwt.emergency_access_ttl', 30);
        $expiresAt = now()->addMinutes($ttlMinutes);
        $action = $requiresSecondAdminApproval ? 'requested' : 'approved';

        $log = EmergencyAccessLog::query()->create([
            'tenant_id' => $tenant->id,
            'platform_admin_user_id' => $requester->id,
            'approved_by_user_id' => $requiresSecondAdminApproval ? null : $requester->id,
            'action' => $action,
            'reason' => $reason,
            'expires_at' => $expiresAt,
            'metadata' => [
                'requires_second_admin_approval' => $requiresSecondAdminApproval,
                'requested_at' => now()->toIso8601String(),
                'requested_by_email' => $requester->email,
                'ttl_minutes' => $ttlMinutes,
            ],
        ]);

        $this->writeAuditLog(
            tenant: $tenant,
            userId: $requester->id,
            action: $requiresSecondAdminApproval ? 'emergency_access_requested' : 'emergency_access_auto_approved',
            description: $requiresSecondAdminApproval
                ? 'Emergency access request created.'
                : 'Emergency access granted without secondary approval.',
            metadata: [
                'emergency_access_log_id' => $log->id,
                'ttl_minutes' => $ttlMinutes,
                'requires_second_admin_approval' => $requiresSecondAdminApproval,
                'reason' => $reason,
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $log;
    }

    public function approve(
        int $logId,
        User $approver,
        ?string $remarks = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): EmergencyAccessLog {
        $this->assertPlatformAdmin($approver);

        $log = EmergencyAccessLog::query()->findOrFail($logId);

        if ($log->action !== 'requested') {
            throw new RuntimeException('Emergency access request is not pending approval.');
        }

        if ($log->expires_at !== null && $log->expires_at->isPast()) {
            $log->update(['action' => 'expired']);

            throw new RuntimeException('Emergency access request has already expired.');
        }

        $requiresSecondApproval = (bool) data_get($log->metadata, 'requires_second_admin_approval', false);

        if ($requiresSecondApproval && $log->platform_admin_user_id === $approver->id) {
            throw new RuntimeException('A different platform admin must approve this emergency access request.');
        }

        $metadata = array_merge($log->metadata ?? [], [
            'approved_at' => now()->toIso8601String(),
            'approval_remarks' => $remarks,
        ]);

        $log->update([
            'approved_by_user_id' => $approver->id,
            'action' => 'approved',
            'metadata' => $metadata,
        ]);

        $tenant = PlatformTenant::query()->findOrFail($log->tenant_id);

        $this->writeAuditLog(
            tenant: $tenant,
            userId: $approver->id,
            action: 'emergency_access_approved',
            description: 'Emergency access request approved.',
            metadata: [
                'emergency_access_log_id' => $log->id,
                'requested_by_user_id' => $log->platform_admin_user_id,
                'remarks' => $remarks,
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $log->fresh() ?? $log;
    }

    public function revoke(
        int $logId,
        User $revoker,
        string $remarks,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): EmergencyAccessLog {
        $this->assertPlatformAdmin($revoker);

        $log = EmergencyAccessLog::query()->findOrFail($logId);

        if (in_array($log->action, ['revoked', 'expired'], true)) {
            throw new RuntimeException('Emergency access has already been closed.');
        }

        $metadata = array_merge($log->metadata ?? [], [
            'revoked_at' => now()->toIso8601String(),
            'revocation_remarks' => $remarks,
            'revoked_by_user_id' => $revoker->id,
        ]);

        $log->update([
            'action' => 'revoked',
            'metadata' => $metadata,
        ]);

        $tenant = PlatformTenant::query()->findOrFail($log->tenant_id);

        $this->writeAuditLog(
            tenant: $tenant,
            userId: $revoker->id,
            action: 'emergency_access_revoked',
            description: 'Emergency access revoked.',
            metadata: [
                'emergency_access_log_id' => $log->id,
                'remarks' => $remarks,
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $log->fresh() ?? $log;
    }

    public function validateApprovedAccess(int $logId, int $tenantId, int $platformAdminUserId): ?EmergencyAccessLog
    {
        /** @var EmergencyAccessLog|null $log */
        $log = EmergencyAccessLog::query()->find($logId);

        if (! $log || $log->tenant_id !== $tenantId || $log->platform_admin_user_id !== $platformAdminUserId) {
            return null;
        }

        if ($log->action !== 'approved') {
            return null;
        }

        if ($log->expires_at !== null && $log->expires_at->isPast()) {
            $log->update(['action' => 'expired']);

            return null;
        }

        return $log;
    }

    public function touchUsage(EmergencyAccessLog $log, string $path, string $method, ?int $userId = null): void
    {
        $log->forceFill([
            'used_at' => now(),
            'metadata' => array_merge($log->metadata ?? [], [
                'last_used_at' => now()->toIso8601String(),
                'last_used_path' => $path,
                'last_used_method' => $method,
            ]),
        ])->save();

        $tenant = PlatformTenant::query()->find($log->tenant_id);

        if ($tenant instanceof PlatformTenant) {
            $this->writeAuditLog(
                tenant: $tenant,
                userId: $userId,
                action: 'emergency_access_used',
                description: 'Sensitive tenant data was accessed under emergency access.',
                metadata: [
                    'emergency_access_log_id' => $log->id,
                    'path' => $path,
                    'method' => $method,
                ]
            );
        }
    }

    protected function assertPlatformAdmin(User $user): void
    {
        $isPlatformAdmin = $user->roles()->where('code', 'super_admin')->exists()
            || PlatformAdmin::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

        if (! $isPlatformAdmin) {
            throw new RuntimeException('Only platform administrators can manage emergency access.');
        }
    }

    protected function assertTenantAllowsEmergencyAccess(PlatformTenant $tenant): void
    {
        if (! (bool) optional($tenant->securitySetting)->emergency_access_enabled) {
            throw new RuntimeException('Emergency access is not enabled for this tenant.');
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function writeAuditLog(
        PlatformTenant $tenant,
        ?int $userId,
        string $action,
        string $description,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $userId,
            'action' => $action,
            'module' => 'emergency_access',
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
