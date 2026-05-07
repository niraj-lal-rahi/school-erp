<?php

namespace App\Modules\Tenant\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class TenantAdminProvisioningService
{
    protected string $tenantConnection = 'tenant';

    /**
     * @var list<string>
     */
    protected array $requiredTables = [
        'schools',
        'users',
        'roles',
        'permissions',
        'role_permissions',
        'user_roles',
        'password_reset_tokens',
    ];

    public function __construct(
        protected TenantConnectionManager $tenantConnections,
    ) {
    }

    /**
     * @param  array<string, mixed>  $adminAttributes
     * @return array<string, mixed>
     */
    public function provision(
        PlatformTenant $tenant,
        array $adminAttributes,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        try {
            $this->tenantConnections->connect($tenant);
            $this->assertTenantSchemaReady($tenant);

            $result = DB::connection($this->tenantConnection)->transaction(function () use (
                $tenant,
                $adminAttributes,
                $performedByUserId,
                $ipAddress,
                $userAgent,
            ): array {
                $schoolId = $this->resolveLocalSchoolId($tenant, $adminAttributes['school_id'] ?? null);

                $user = $this->createOrUpdateAdminUser($tenant, $schoolId, $adminAttributes);
                $role = $this->createOrUpdateTenantAdminRole($schoolId);

                $permissionSync = $this->syncDefaultPermissions($schoolId, (int) $role['id']);
                $this->assignTenantAdminRole($schoolId, (int) $user['id'], (int) $role['id'], $performedByUserId);

                $invite = $this->createPasswordResetInvite($tenant, $schoolId, (string) $user['email']);

                $this->logAction(
                    $tenant,
                    'tenant_admin_provisioned',
                    'tenant_identity',
                    'Tenant admin user provisioned successfully.',
                    [
                        'school_id' => $schoolId,
                        'user_id' => $user['id'],
                        'role_id' => $role['id'],
                        'permission_count' => $permissionSync['count'],
                        'email' => $user['email'],
                    ],
                    $performedByUserId,
                    $ipAddress,
                    $userAgent,
                );

                return [
                    'school_id' => $schoolId,
                    'user' => $user,
                    'role' => $role,
                    'invite' => $invite,
                    'permission_count' => $permissionSync['count'],
                ];
            });

            $this->sendInviteEmail($tenant, $result['user'], $result['invite']);

            $this->logAction(
                $tenant,
                'tenant_admin_invite_sent',
                'tenant_identity',
                'Tenant admin invite email sent.',
                [
                    'school_id' => $result['school_id'],
                    'user_id' => $result['user']['id'],
                    'email' => $result['user']['email'],
                ],
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            return [
                'success' => true,
                'school_id' => $result['school_id'],
                'user_id' => $result['user']['id'],
                'role_id' => $result['role']['id'],
                'email' => $result['user']['email'],
                'permission_count' => $result['permission_count'],
                'password_reset_expires_minutes' => $result['invite']['expires_in_minutes'],
            ];
        } catch (Throwable $exception) {
            $this->logAction(
                $tenant,
                'tenant_admin_provisioning_failed',
                'tenant_identity',
                'Tenant admin provisioning failed.',
                [
                    'error' => $exception->getMessage(),
                    'admin_email' => $adminAttributes['email'] ?? null,
                ],
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            throw $exception;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    protected function assertTenantSchemaReady(PlatformTenant $tenant): void
    {
        foreach ($this->requiredTables as $table) {
            if (! Schema::connection($this->tenantConnection)->hasTable($table)) {
                throw new \RuntimeException(sprintf(
                    'Tenant schema is not ready for tenant [%s]. Missing table: %s',
                    $tenant->code,
                    $table
                ));
            }
        }
    }

    protected function resolveLocalSchoolId(PlatformTenant $tenant, mixed $preferredSchoolId = null): int
    {
        $connection = DB::connection($this->tenantConnection);

        if ($preferredSchoolId !== null) {
            $schoolId = (int) $preferredSchoolId;
            $exists = $connection->table('schools')->where('id', $schoolId)->exists();

            if ($exists) {
                return $schoolId;
            }
        }

        $schoolId = $connection->table('schools')
            ->where('code', $tenant->code)
            ->value('id');

        if ($schoolId) {
            return (int) $schoolId;
        }

        $schoolId = $connection->table('schools')
            ->where('slug', $tenant->slug)
            ->value('id');

        if ($schoolId) {
            return (int) $schoolId;
        }

        $schoolId = $connection->table('schools')
            ->orderBy('id')
            ->value('id');

        if ($schoolId) {
            return (int) $schoolId;
        }

        throw new \RuntimeException(sprintf('No tenant school record found for tenant [%s].', $tenant->code));
    }

    /**
     * @param  array<string, mixed>  $adminAttributes
     * @return array<string, mixed>
     */
    protected function createOrUpdateAdminUser(PlatformTenant $tenant, int $schoolId, array $adminAttributes): array
    {
        $connection = DB::connection($this->tenantConnection);
        $now = now();
        $email = Str::lower(trim((string) ($adminAttributes['email'] ?? '')));
        $firstName = trim((string) ($adminAttributes['first_name'] ?? ''));
        $lastName = trim((string) ($adminAttributes['last_name'] ?? ''));

        if ($email === '' || $firstName === '' || $lastName === '') {
            throw new \InvalidArgumentException('Tenant admin first name, last name, and email are required.');
        }

        $fullName = trim($firstName.' '.$lastName);

        $existingUser = $connection->table('users')
            ->where('school_id', $schoolId)
            ->where('email', $email)
            ->first();

        if ($existingUser) {
            $connection->table('users')
                ->where('id', $existingUser->id)
                ->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'name' => $fullName,
                    'phone' => $adminAttributes['phone'] ?? $existingUser->phone,
                    'status' => $adminAttributes['status'] ?? $existingUser->status ?? 'active',
                    'updated_at' => $now,
                ]);

            return [
                'id' => (int) $existingUser->id,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $fullName,
                'created' => false,
            ];
        }

        $temporaryPassword = Str::password(40, true, true, true, false);

        $userId = (int) $connection->table('users')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'school_id' => $schoolId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $fullName,
            'email' => $email,
            'phone' => $adminAttributes['phone'] ?? null,
            // Force the invite path by using an unknown temporary password.
            'password' => Hash::make($temporaryPassword),
            'status' => $adminAttributes['status'] ?? 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => $userId,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $fullName,
            'created' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function createOrUpdateTenantAdminRole(int $schoolId): array
    {
        $connection = DB::connection($this->tenantConnection);
        $now = now();

        $existingRole = $connection->table('roles')
            ->where('school_id', $schoolId)
            ->where('code', 'tenant_admin')
            ->first();

        if ($existingRole) {
            $connection->table('roles')
                ->where('id', $existingRole->id)
                ->update([
                    'name' => 'Tenant Administrator',
                    'slug' => 'tenant_admin',
                    'scope' => 'tenant',
                    'description' => 'Tenant administrator with full school-level operational access.',
                    'role_type' => 'tenant',
                    'is_default' => true,
                    'status' => 'active',
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);

            return [
                'id' => (int) $existingRole->id,
                'created' => false,
            ];
        }

        $roleId = (int) $connection->table('roles')->insertGetId([
            'school_id' => $schoolId,
            'uuid' => (string) Str::uuid(),
            'name' => 'Tenant Administrator',
            'code' => 'tenant_admin',
            'slug' => 'tenant_admin',
            'scope' => 'tenant',
            'description' => 'Tenant administrator with full school-level operational access.',
            'role_type' => 'tenant',
            'is_default' => true,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => $roleId,
            'created' => true,
        ];
    }

    /**
     * @return array{count:int}
     */
    protected function syncDefaultPermissions(int $schoolId, int $roleId): array
    {
        $connection = DB::connection($this->tenantConnection);
        $now = now();

        $permissionIds = $connection->table('permissions')
            ->where(function ($query): void {
                $query->where('status', 'active')
                    ->orWhereNull('status');
            })
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        if ($permissionIds === []) {
            throw new \RuntimeException('No tenant permissions are available to assign to tenant_admin.');
        }

        $connection->table('role_permissions')
            ->where('role_id', $roleId)
            ->whereNotIn('permission_id', $permissionIds)
            ->delete();

        foreach ($permissionIds as $permissionId) {
            $connection->table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ],
                [
                    'school_id' => $schoolId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        return [
            'count' => count($permissionIds),
        ];
    }

    protected function assignTenantAdminRole(
        int $schoolId,
        int $userId,
        int $roleId,
        ?int $assignedByUserId = null,
    ): void {
        DB::connection($this->tenantConnection)->table('user_roles')->updateOrInsert(
            [
                'school_id' => $schoolId,
                'user_id' => $userId,
                'role_id' => $roleId,
            ],
            [
                'assigned_by' => $assignedByUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function createPasswordResetInvite(PlatformTenant $tenant, int $schoolId, string $email): array
    {
        $plainToken = Str::random(64);
        $expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
        $createdAt = now();

        DB::connection($this->tenantConnection)->table(config('auth.passwords.users.table', 'password_reset_tokens'))
            ->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($plainToken),
                    'created_at' => $createdAt,
                ]
            );

        return [
            'email' => $email,
            'plain_token' => $plainToken,
            'expires_in_minutes' => $expiresInMinutes,
            'reset_url' => $this->buildResetUrl($tenant, $schoolId, $email, $plainToken),
        ];
    }

    /**
     * @param  array<string, mixed>  $user
     * @param  array<string, mixed>  $invite
     */
    protected function sendInviteEmail(PlatformTenant $tenant, array $user, array $invite): void
    {
        $tenantName = $tenant->name;
        $subject = sprintf('You have been invited to manage %s', $tenantName);

        $message = implode("\n\n", [
            sprintf('Hello %s,', $user['first_name']),
            sprintf('Your tenant administrator access for %s is ready.', $tenantName),
            'To activate your account, set your password using the secure link below:',
            $invite['reset_url'],
            sprintf('This link expires in %d minutes.', (int) $invite['expires_in_minutes']),
            sprintf('Tenant code: %s', $tenant->code),
        ]);

        Mail::raw($message, function ($mail) use ($invite, $subject): void {
            $mail->to((string) $invite['email'])
                ->subject($subject);
        });
    }

    protected function buildResetUrl(
        PlatformTenant $tenant,
        int $schoolId,
        string $email,
        string $token,
    ): string {
        $baseUrl = rtrim((string) (
            env('TENANT_APP_URL')
            ?: env('FRONTEND_URL')
            ?: env('ADMIN_APP_URL')
            ?: env('APP_URL', 'http://localhost')
        ), '/');

        $query = http_build_query([
            'token' => $token,
            'email' => $email,
            'tenant' => $tenant->code,
            'tenant_slug' => $tenant->slug,
            'school_id' => $schoolId,
        ]);

        return $baseUrl.'/reset-password?'.$query;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        PlatformTenant $tenant,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenant->id,
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
