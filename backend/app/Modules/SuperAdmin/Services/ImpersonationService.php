<?php

namespace App\Modules\SuperAdmin\Services;

use App\Models\User;
use App\Modules\SuperAdmin\Models\EmergencyAccessLog;
use App\Modules\SuperAdmin\Models\PlatformAdmin;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantImpersonationLog;
use App\Modules\Tenant\Services\TenantConnectionManager;
use App\Support\Auth\JwtManager;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ImpersonationService
{
    protected string $tenantConnection = 'tenant';

    public function __construct(
        protected TenantConnectionManager $tenantConnections,
        protected JwtManager $jwtManager,
        protected EmergencyAccessService $emergencyAccess,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function start(
        PlatformTenant $tenant,
        User $platformAdmin,
        string $reason,
        bool $emergencyAccess = false,
        ?int $emergencyAccessLogId = null,
        ?int $ttlMinutes = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $this->assertPlatformAdmin($platformAdmin);
        $this->assertTenantCanBeImpersonated($tenant);

        if ($emergencyAccess) {
            if (! (bool) optional($tenant->securitySetting)->emergency_access_enabled) {
                throw new RuntimeException('Emergency access is not enabled for this tenant.');
            }

            if (! $emergencyAccessLogId) {
                throw new RuntimeException('An approved emergency access record is required.');
            }

            $approvedAccess = $this->emergencyAccess->validateApprovedAccess(
                logId: $emergencyAccessLogId,
                tenantId: $tenant->id,
                platformAdminUserId: $platformAdmin->id,
            );

            if (! $approvedAccess instanceof EmergencyAccessLog) {
                throw new RuntimeException('Emergency access approval is missing, expired, or invalid.');
            }
        }

        $ttlMinutes ??= (int) config('erp.jwt.impersonation_ttl', 30);
        $expiresAt = now()->addMinutes($ttlMinutes)->toIso8601String();

        try {
            $this->tenantConnections->connect($tenant);

            $impersonatedUser = $this->resolveTenantAdminUser();

            $log = TenantImpersonationLog::query()->create([
                'tenant_id' => $tenant->id,
                'platform_admin_user_id' => $platformAdmin->id,
                'impersonated_user_id' => $impersonatedUser['id'],
                'status' => 'active',
                'reason' => $reason,
                'started_at' => now(),
                'metadata' => [
                    'emergency_access' => $emergencyAccess,
                    'emergency_access_log_id' => $emergencyAccessLogId,
                    'ttl_minutes' => $ttlMinutes,
                    'expires_at' => $expiresAt,
                    'platform_admin_email' => $platformAdmin->email,
                    'impersonated_user_email' => $impersonatedUser['email'],
                    'tenant_code' => $tenant->code,
                    'tenant_slug' => $tenant->slug,
                ],
            ]);

            $accessToken = $this->jwtManager->issueAccessToken(
                $this->buildAuthenticatableUser($impersonatedUser),
                [
                    'platform_tenant_id' => $tenant->id,
                    'impersonation_log_id' => $log->id,
                    'impersonator_user_id' => $platformAdmin->id,
                    'impersonation_reason' => $reason,
                    'impersonation_emergency_access' => $emergencyAccess,
                    'emergency_access_log_id' => $emergencyAccessLogId,
                    'impersonation_started_at' => $log->started_at?->timestamp,
                    'impersonation_banner' => [
                        'active' => true,
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'tenant_code' => $tenant->code,
                        'reason' => $reason,
                        'emergency_access' => $emergencyAccess,
                        'emergency_access_log_id' => $emergencyAccessLogId,
                        'expires_at' => $expiresAt,
                        'impersonator' => [
                            'id' => $platformAdmin->id,
                            'name' => $platformAdmin->name,
                            'email' => $platformAdmin->email,
                        ],
                    ],
                ],
                $ttlMinutes
            );

            $this->writeAuditLog(
                tenant: $tenant,
                userId: $platformAdmin->id,
                action: 'tenant_impersonation_started',
                description: 'Tenant impersonation session started.',
                metadata: [
                    'impersonation_log_id' => $log->id,
                    'impersonated_user_id' => $impersonatedUser['id'],
                    'impersonated_user_email' => $impersonatedUser['email'],
                    'emergency_access' => $emergencyAccess,
                    'emergency_access_log_id' => $emergencyAccessLogId,
                    'ttl_minutes' => $ttlMinutes,
                    'reason' => $reason,
                ],
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            return [
                'access_token' => $accessToken,
                'token_type' => 'Bearer',
                'expires_in_minutes' => $ttlMinutes,
                'impersonation' => [
                    'id' => $log->id,
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'tenant_code' => $tenant->code,
                    'reason' => $reason,
                    'emergency_access' => $emergencyAccess,
                    'emergency_access_log_id' => $emergencyAccessLogId,
                    'started_at' => $log->started_at?->toIso8601String(),
                    'expires_at' => $log->metadata['expires_at'] ?? null,
                ],
                'user' => [
                    'id' => $impersonatedUser['id'],
                    'school_id' => $impersonatedUser['school_id'],
                    'name' => $impersonatedUser['name'],
                    'email' => $impersonatedUser['email'],
                ],
            ];
        } catch (Throwable $exception) {
            $this->writeAuditLog(
                tenant: $tenant,
                userId: $platformAdmin->id,
                action: 'tenant_impersonation_failed',
                description: 'Tenant impersonation session failed to start.',
                metadata: [
                    'reason' => $reason,
                    'emergency_access' => $emergencyAccess,
                    'emergency_access_log_id' => $emergencyAccessLogId,
                    'error' => $exception->getMessage(),
                ],
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );

            throw $exception;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    public function stop(
        User $actingUser,
        int $impersonationLogId,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): TenantImpersonationLog {
        $log = TenantImpersonationLog::query()->findOrFail($impersonationLogId);

        if ($log->status !== 'active' || $log->ended_at !== null) {
            throw new RuntimeException('Impersonation session is no longer active.');
        }

        $metadata = $log->metadata ?? [];
        $metadata['stopped_by_user_id'] = $actingUser->id;
        $metadata['stopped_at'] = now()->toIso8601String();

        $log->update([
            'status' => 'stopped',
            'ended_at' => now(),
            'metadata' => $metadata,
        ]);

        $tenant = PlatformTenant::query()->find($log->tenant_id);

        if ($tenant instanceof PlatformTenant) {
            $this->writeAuditLog(
                tenant: $tenant,
                userId: $actingUser->id,
                action: 'tenant_impersonation_stopped',
                description: 'Tenant impersonation session stopped.',
                metadata: [
                    'impersonation_log_id' => $log->id,
                    'impersonated_user_id' => $log->impersonated_user_id,
                ],
                ipAddress: $ipAddress,
                userAgent: $userAgent,
            );
        }

        return $log->fresh() ?? $log;
    }

    public function validateActiveSession(int $impersonationLogId): ?TenantImpersonationLog
    {
        /** @var TenantImpersonationLog|null $log */
        $log = TenantImpersonationLog::query()->find($impersonationLogId);

        if (! $log || $log->status !== 'active' || $log->ended_at !== null) {
            return null;
        }

        $expiresAt = data_get($log->metadata, 'expires_at');

        if (is_string($expiresAt) && now()->greaterThan(CarbonImmutable::parse($expiresAt))) {
            $log->update([
                'status' => 'expired',
                'ended_at' => now(),
            ]);

            return null;
        }

        return $log;
    }

    protected function assertPlatformAdmin(User $user): void
    {
        $isPlatformAdmin = $user->roles()->where('code', 'super_admin')->exists()
            || PlatformAdmin::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();

        if (! $isPlatformAdmin) {
            throw new RuntimeException('Only platform administrators can start impersonation.');
        }
    }

    protected function assertTenantCanBeImpersonated(PlatformTenant $tenant): void
    {
        if (! in_array($tenant->status, ['trial', 'active'], true)) {
            throw new RuntimeException('Only active or trial tenants can be impersonated.');
        }
    }

    /**
     * @return array{id:int,school_id:int,name:string,email:string}
     */
    protected function resolveTenantAdminUser(): array
    {
        $user = DB::connection($this->tenantConnection)
            ->table('users')
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.code', 'tenant_admin')
            ->where('users.status', 'active')
            ->orderBy('users.id')
            ->select([
                'users.id',
                'users.school_id',
                'users.name',
                'users.first_name',
                'users.last_name',
                'users.email',
            ])
            ->first();

        if (! $user) {
            throw new RuntimeException('No active tenant admin user is available for impersonation.');
        }

        $name = trim((string) ($user->name ?: trim(($user->first_name ?? '').' '.($user->last_name ?? ''))));

        return [
            'id' => (int) $user->id,
            'school_id' => (int) $user->school_id,
            'name' => $name,
            'email' => (string) $user->email,
        ];
    }

    /**
     * @param  array{id:int,school_id:int,name:string,email:string}  $userData
     */
    protected function buildAuthenticatableUser(array $userData): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $userData['id'],
            'school_id' => $userData['school_id'],
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);
        $user->exists = true;

        return $user;
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
            'module' => 'tenant_impersonation',
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }
}
