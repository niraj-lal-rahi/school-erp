<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Models\TenantDatabaseConnection;
use App\Modules\SuperAdmin\Models\TenantFeatureAccess;
use App\Modules\SuperAdmin\Models\TenantSecuritySetting;
use App\Modules\SuperAdmin\Models\TenantSubscription;
use App\Modules\Tenant\Services\TenantAdminProvisioningService;
use App\Modules\Tenant\Services\TenantConnectionManager;
use App\Modules\Tenant\Services\TenantMigrationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantDatabaseProvisioningService
{
    protected string $platformConnection = 'platform';

    protected string $tenantConnection = 'tenant';

    /**
     * @var array<string, bool|string|int|null>
     */
    protected array $provisioningState = [
        'database_created' => false,
        'database_user_created' => false,
        'tenant_record_created' => false,
        'connection_record_created' => false,
        'tenant_connected' => false,
        'local_school_created' => false,
        'database_name' => null,
        'database_username' => null,
    ];

    public function __construct(
        protected TenantConnectionManager $tenantConnections,
        protected TenantMigrationService $tenantMigrations,
        protected TenantAdminProvisioningService $tenantAdmins,
    ) {
    }

    /**
     * @param  array<string, mixed>  $tenantAttributes
     * @param  array<string, mixed>  $adminAttributes
     * @return array<string, mixed>
     */
    public function provision(
        array $tenantAttributes,
        array $adminAttributes,
        ?SubscriptionPlan $plan = null,
        int $trialDays = 14,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $this->resetState();

        $this->logStep('tenant_provisioning_started', null, 'tenant_provisioning', 'Tenant provisioning started.', [
            'tenant_code' => $tenantAttributes['code'] ?? null,
            'tenant_slug' => $tenantAttributes['slug'] ?? null,
        ], $performedByUserId, $ipAddress, $userAgent);

        $platformTenant = $this->createPlatformTenant($tenantAttributes, $trialDays, $performedByUserId, $ipAddress, $userAgent);
        $this->provisioningState['tenant_record_created'] = true;

        try {
            return $this->provisionDatabaseForTenant(
                $platformTenant,
                $adminAttributes,
                $tenantAttributes,
                $plan,
                $trialDays,
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );
        } catch (\Throwable $exception) {
            $this->rollbackProvisioning(
                $platformTenant,
                $this->provisioningState['database_name'] ?: null,
                $this->provisioningState['database_username'] ?: null,
                $performedByUserId,
                $ipAddress,
                $userAgent,
                $exception
            );

            throw $exception;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    /**
     * @param  array<string, mixed>  $adminAttributes
     * @param  array<string, mixed>  $tenantAttributes
     * @return array<string, mixed>
     */
    public function provisionDatabaseForTenant(
        PlatformTenant $platformTenant,
        array $adminAttributes,
        array $tenantAttributes = [],
        ?SubscriptionPlan $plan = null,
        int $trialDays = 14,
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): array {
        $databaseName = null;
        $databaseUsername = null;
        $databasePassword = null;

        if (! $this->provisioningState['tenant_record_created']) {
            $this->resetState();
        }

        if ($platformTenant->activeDatabaseConnection()->exists()) {
            throw new \RuntimeException(sprintf(
                'Tenant [%s] already has an active database connection.',
                $platformTenant->code
            ));
        }

        try {
            $databaseName = $this->generateDatabaseName($platformTenant);
            $databaseUsername = $this->generateDatabaseUsername($platformTenant);
            $databasePassword = $this->generateDatabasePassword();
            $this->provisioningState['database_name'] = $databaseName;
            $this->provisioningState['database_username'] = $databaseUsername;

            $this->logStep('tenant_db_credentials_generated', $platformTenant->id, 'tenant_database', 'Tenant database credentials generated.', [
                'database_name' => $databaseName,
                'database_username' => $databaseUsername,
            ], $performedByUserId, $ipAddress, $userAgent);

            $this->createDatabase($databaseName);
            $this->provisioningState['database_created'] = true;

            [$databaseUsername, $databasePassword] = $this->createDatabaseUserAndPrivileges(
                $databaseName,
                $databaseUsername,
                $databasePassword,
                $platformTenant,
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            $this->provisioningState['database_username'] = $databaseUsername;

            $connection = $this->storeTenantDatabaseConnection(
                $platformTenant,
                $databaseName,
                $databaseUsername,
                $databasePassword,
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );
            $this->provisioningState['connection_record_created'] = true;

            TenantSecuritySetting::query()->updateOrCreate(
                ['tenant_id' => $platformTenant->id],
                [
                    'encryption_enabled' => true,
                    'database_isolated' => true,
                    'emergency_access_enabled' => false,
                    'backup_encryption_enabled' => true,
                    'status' => 'active',
                ]
            );

            $this->ensureTenantConnectionIsReachable($platformTenant, $performedByUserId, $ipAddress, $userAgent);

            $migrationResult = $this->tenantMigrations->runMigrations($platformTenant);

            if (! ($migrationResult['success'] ?? false)) {
                throw new \RuntimeException('Tenant migrations failed: '.implode('; ', $migrationResult['errors'] ?? []));
            }

            $localSchoolId = $this->executeOnTenant($platformTenant, function () use ($platformTenant, $tenantAttributes): int {
                return $this->createLocalSchoolRecord($platformTenant, $tenantAttributes);
            });
            $this->provisioningState['local_school_created'] = true;

            $seedResult = $this->tenantMigrations->runSeeders($platformTenant);

            if (! ($seedResult['success'] ?? false)) {
                throw new \RuntimeException('Tenant seeders failed: '.json_encode($seedResult['errors'] ?? ['Unknown seeding failure'], JSON_THROW_ON_ERROR));
            }

            $this->createDefaultSettings($localSchoolId, $platformTenant, $tenantAttributes, $performedByUserId, $ipAddress, $userAgent);
            $adminProvisioning = $this->tenantAdmins->provision(
                $platformTenant,
                array_merge($adminAttributes, ['school_id' => $localSchoolId]),
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            $localAdminUserId = (int) $adminProvisioning['user_id'];
            $tenantAdminRoleId = (int) $adminProvisioning['role_id'];

            $subscription = $this->createTrialSubscription(
                $platformTenant,
                $plan,
                $trialDays,
                $performedByUserId,
                $ipAddress,
                $userAgent,
            );

            $this->logStep('tenant_provisioning_completed', $platformTenant->id, 'tenant_provisioning', 'Tenant provisioning completed successfully.', [
                'database_name' => $databaseName,
                'database_username' => $databaseUsername,
                'local_school_id' => $localSchoolId,
                'local_admin_user_id' => $localAdminUserId,
                'tenant_admin_role_id' => $tenantAdminRoleId,
                'migration_output' => $migrationResult['output'] ?? null,
                'subscription_id' => $subscription->id,
                'connection_id' => $connection->id,
            ], $performedByUserId, $ipAddress, $userAgent);

            return [
                'platform_tenant' => $platformTenant,
                'database_connection' => $connection,
                'subscription' => $subscription,
                'local_school_id' => $localSchoolId,
                'local_admin_user_id' => $localAdminUserId,
                'tenant_admin_role_id' => $tenantAdminRoleId,
            ];
        } catch (\Throwable $exception) {
            $this->rollbackProvisioning(
                $platformTenant,
                $databaseName,
                $databaseUsername,
                $performedByUserId,
                $ipAddress,
                $userAgent,
                $exception
            );

            throw $exception;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    protected function createPlatformTenant(
        array $attributes,
        int $trialDays,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): PlatformTenant {
        $tenant = PlatformTenant::query()->create([
            'name' => $attributes['name'],
            'code' => $attributes['code'],
            'slug' => $attributes['slug'] ?? Str::slug((string) $attributes['name']),
            'email' => $attributes['email'] ?? null,
            'phone' => $attributes['phone'] ?? null,
            'status' => $attributes['status'] ?? 'trial',
            'trial_ends_at' => $attributes['trial_ends_at'] ?? now()->addDays($trialDays),
            'activated_at' => $attributes['activated_at'] ?? null,
            'suspended_at' => null,
        ]);

        $this->logStep('platform_tenant_created', $tenant->id, 'platform_tenant', 'Platform tenant record created.', [
            'tenant_code' => $tenant->code,
            'tenant_slug' => $tenant->slug,
        ], $performedByUserId, $ipAddress, $userAgent);

        return $tenant;
    }

    protected function generateDatabaseName(PlatformTenant $tenant): string
    {
        $prefix = (string) env('TENANT_DB_NAME_PREFIX', 'erp_school_');

        return $this->sanitizeIdentifier($prefix.sprintf('%03d', $tenant->id), 64);
    }

    protected function generateDatabaseUsername(PlatformTenant $tenant): string
    {
        $prefix = (string) env('TENANT_DB_USER_PREFIX', 'erp_tnt_');
        $suffix = Str::lower(Str::random(6));

        return $this->sanitizeIdentifier($prefix.$tenant->id.'_'.$suffix, 32);
    }

    protected function generateDatabasePassword(): string
    {
        return Str::password(32, true, true, true, false);
    }

    protected function createDatabase(string $databaseName): void
    {
        DB::connection($this->platformConnection)->statement(
            sprintf(
                'CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                $this->escapeIdentifier($databaseName)
            )
        );
    }

    /**
     * @return array{0:string,1:string}
     */
    protected function createDatabaseUserAndPrivileges(
        string $databaseName,
        string $databaseUsername,
        string $databasePassword,
        PlatformTenant $tenant,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): array {
        $connection = DB::connection($this->platformConnection);
        $allowDedicatedUser = filter_var(env('TENANT_DB_PROVISION_CREATE_USER', true), FILTER_VALIDATE_BOOL);
        $grantHost = (string) env('TENANT_DB_GRANT_HOST', '%');

        if (! $allowDedicatedUser) {
            $fallbackUsername = (string) (env('TENANT_DB_USERNAME') ?: env('PLATFORM_DB_USERNAME') ?: env('DB_USERNAME'));
            $fallbackPassword = (string) (env('TENANT_DB_PASSWORD') ?: env('PLATFORM_DB_PASSWORD') ?: env('DB_PASSWORD'));

            $connection->statement(
                sprintf(
                    'GRANT ALL PRIVILEGES ON `%s`.* TO %s@%s',
                    $this->escapeIdentifier($databaseName),
                    $this->quoteSqlString($fallbackUsername),
                    $this->quoteSqlString($grantHost),
                )
            );
            $connection->statement('FLUSH PRIVILEGES');

            $this->logStep('tenant_db_user_fallback_granted', $tenant->id, 'tenant_database', 'Tenant database privileges granted using fallback DB user.', [
                'database_name' => $databaseName,
                'database_username' => $fallbackUsername,
            ], $performedByUserId, $ipAddress, $userAgent);

            return [$fallbackUsername, $fallbackPassword];
        }

        $connection->statement(
            sprintf(
                'CREATE USER %s@%s IDENTIFIED BY %s',
                $this->quoteSqlString($databaseUsername),
                $this->quoteSqlString($grantHost),
                $this->quoteSqlString($databasePassword),
            )
        );
        $this->provisioningState['database_user_created'] = true;

        $connection->statement(
            sprintf(
                'GRANT ALL PRIVILEGES ON `%s`.* TO %s@%s',
                $this->escapeIdentifier($databaseName),
                $this->quoteSqlString($databaseUsername),
                $this->quoteSqlString($grantHost),
            )
        );
        $connection->statement('FLUSH PRIVILEGES');

        $this->logStep('tenant_db_user_created', $tenant->id, 'tenant_database', 'Tenant database user created and granted privileges.', [
            'database_name' => $databaseName,
            'database_username' => $databaseUsername,
        ], $performedByUserId, $ipAddress, $userAgent);

        return [$databaseUsername, $databasePassword];
    }

    protected function storeTenantDatabaseConnection(
        PlatformTenant $tenant,
        string $databaseName,
        string $databaseUsername,
        string $databasePassword,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): TenantDatabaseConnection {
        $connection = TenantDatabaseConnection::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'connection_name' => 'tenant',
            ],
            [
                'database_name' => $databaseName,
                'database_host' => (string) env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
                'database_port' => (string) env('TENANT_DB_PORT', env('DB_PORT', '3306')),
                'database_username' => $databaseUsername,
                'database_password' => $databasePassword,
                'database_driver' => (string) env('TENANT_DB_CONNECTION', 'mysql'),
                'is_active' => true,
                'connection_status' => 'unknown',
            ]
        );

        $this->logStep('tenant_db_connection_stored', $tenant->id, 'tenant_database', 'Tenant database connection stored.', [
            'connection_id' => $connection->id,
            'database_name' => $databaseName,
            'database_username' => $databaseUsername,
        ], $performedByUserId, $ipAddress, $userAgent);

        return $connection;
    }

    protected function ensureTenantConnectionIsReachable(
        PlatformTenant $tenant,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        if (! $this->tenantConnections->testConnection($tenant)) {
            throw new \RuntimeException('Tenant database connection test failed.');
        }

        $this->logStep('tenant_db_connection_tested', $tenant->id, 'tenant_database', 'Tenant database connection tested successfully.', [], $performedByUserId, $ipAddress, $userAgent);
    }

    protected function createLocalSchoolRecord(PlatformTenant $tenant, array $tenantAttributes): int
    {
        $connection = DB::connection($this->tenantConnection);
        $now = now();

        $existingId = $connection->table('schools')
            ->where('code', $tenant->code)
            ->value('id');

        if ($existingId) {
            return (int) $existingId;
        }

        return (int) $connection->table('schools')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'name' => $tenant->name,
            'code' => $tenant->code,
            'slug' => $tenant->slug,
            'domain' => $tenantAttributes['domain'] ?? null,
            'timezone' => $tenantAttributes['timezone'] ?? 'Asia/Kolkata',
            'locale' => $tenantAttributes['locale'] ?? 'en',
            'status' => 'active',
            'settings' => json_encode([
                'currency' => $tenantAttributes['currency'] ?? 'INR',
                'country' => $tenantAttributes['country'] ?? 'IN',
                'saas_onboarded' => true,
            ], JSON_THROW_ON_ERROR),
            'storage_disk' => $tenantAttributes['storage_disk'] ?? 'private',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function createDefaultSettings(
        int $localSchoolId,
        PlatformTenant $tenant,
        array $tenantAttributes,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): void {
        $connection = DB::connection($this->tenantConnection);
        $now = now();

        $generalGroupId = $connection->table('setting_groups')
            ->where('school_id', $localSchoolId)
            ->where('code', 'general')
            ->value('id');

        if ($generalGroupId) {
            $connection->table('settings')->updateOrInsert(
                [
                    'school_id' => $localSchoolId,
                    'scope' => 'tenant',
                    'key' => 'school.portal_welcome_message',
                ],
                [
                    'group_id' => $generalGroupId,
                    'value' => sprintf('Welcome to %s', $tenant->name),
                    'value_type' => 'string',
                    'is_sensitive' => false,
                    'is_public' => true,
                    'description' => 'Portal welcome message.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $connection->table('branding_settings')->updateOrInsert(
            ['school_id' => $localSchoolId],
            [
                'school_name' => $tenant->name,
                'primary_color' => '#0F4C81',
                'secondary_color' => '#1C7C54',
                'accent_color' => '#F4B400',
                'footer_text' => sprintf('%s | Learning with confidence', $tenant->name),
                'custom_css' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $connection->table('localization_settings')->updateOrInsert(
            ['school_id' => $localSchoolId],
            [
                'timezone' => $tenantAttributes['timezone'] ?? 'Asia/Kolkata',
                'locale' => $tenantAttributes['locale'] ?? 'en',
                'date_format' => 'd-m-Y',
                'time_format' => 'h:i A',
                'currency' => $tenantAttributes['currency'] ?? 'INR',
                'currency_symbol' => '₹',
                'first_day_of_week' => 'monday',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $connection->table('security_settings')->updateOrInsert(
            ['school_id' => $localSchoolId],
            [
                'password_min_length' => 8,
                'password_requires_uppercase' => true,
                'password_requires_number' => true,
                'password_requires_symbol' => false,
                'session_timeout_minutes' => 120,
                'max_login_attempts' => 5,
                'lockout_minutes' => 15,
                'two_factor_enabled' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->logStep('tenant_default_settings_created', $tenant->id, 'tenant_settings', 'Default tenant settings created.', [
            'local_school_id' => $localSchoolId,
        ], $performedByUserId, $ipAddress, $userAgent);
    }

    protected function createTrialSubscription(
        PlatformTenant $tenant,
        ?SubscriptionPlan $plan,
        int $trialDays,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
    ): TenantSubscription {
        $plan ??= SubscriptionPlan::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if (! $plan) {
            throw new \RuntimeException('No active subscription plan is available for tenant provisioning.');
        }

        $subscription = TenantSubscription::query()->create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'subscription_code' => 'SUB-'.Str::upper(Str::random(10)),
            'billing_cycle' => 'monthly',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays($trialDays)->toDateString(),
            'trial_ends_at' => now()->addDays($trialDays),
            'status' => 'trial',
            'auto_renew' => true,
            'next_billing_at' => now()->addDays($trialDays),
        ]);

        foreach ($plan->features()->get() as $feature) {
            TenantFeatureAccess::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'feature_code' => $feature->feature_code,
                ],
                [
                    'subscription_plan_id' => $plan->id,
                    'module' => $feature->module,
                    'is_enabled' => $feature->is_enabled,
                    'limit_value' => $feature->limit_value,
                    'access_source' => 'plan',
                    'metadata' => $feature->config ?? [],
                ]
            );
        }

        $this->logStep('tenant_trial_subscription_created', $tenant->id, 'tenant_subscription', 'Trial subscription created.', [
            'subscription_id' => $subscription->id,
            'subscription_plan_id' => $plan->id,
            'trial_days' => $trialDays,
        ], $performedByUserId, $ipAddress, $userAgent);

        return $subscription;
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function executeOnTenant(PlatformTenant $tenant, callable $callback): mixed
    {
        try {
            $this->tenantConnections->connect($tenant);
            $this->provisioningState['tenant_connected'] = true;

            return $callback();
        } finally {
            $this->tenantConnections->disconnect();
            $this->provisioningState['tenant_connected'] = false;
        }
    }

    protected function rollbackProvisioning(
        ?PlatformTenant $tenant,
        ?string $databaseName,
        ?string $databaseUsername,
        ?int $performedByUserId,
        ?string $ipAddress,
        ?string $userAgent,
        \Throwable $exception,
    ): void {
        try {
            if ($this->provisioningState['tenant_connected']) {
                $this->tenantConnections->disconnect();
            }
        } catch (\Throwable) {
            // Ignore disconnect failures during rollback.
        }

        if ($databaseName && $this->provisioningState['database_created']) {
            try {
                DB::connection($this->platformConnection)->statement(
                    sprintf('DROP DATABASE IF EXISTS `%s`', $this->escapeIdentifier($databaseName))
                );
            } catch (\Throwable) {
                // Ignore and continue rollback logging.
            }
        }

        if ($databaseUsername && $this->provisioningState['database_user_created']) {
            try {
                $grantHost = (string) env('TENANT_DB_GRANT_HOST', '%');

                DB::connection($this->platformConnection)->statement(
                    sprintf(
                        'DROP USER IF EXISTS %s@%s',
                        $this->quoteSqlString($databaseUsername),
                        $this->quoteSqlString($grantHost),
                    )
                );
                DB::connection($this->platformConnection)->statement('FLUSH PRIVILEGES');
            } catch (\Throwable) {
                // Ignore and continue rollback logging.
            }
        }

        if ($tenant) {
            try {
                TenantDatabaseConnection::query()
                    ->where('tenant_id', $tenant->id)
                    ->update([
                        'is_active' => false,
                        'connection_status' => 'failed',
                    ]);

                $tenant->update([
                    'status' => 'suspended',
                    'suspended_at' => now(),
                ]);
            } catch (\Throwable) {
                // Ignore and continue rollback logging.
            }
        }

        $this->logStep('tenant_provisioning_failed', $tenant?->id, 'tenant_provisioning', 'Tenant provisioning failed.', [
            'database_name' => $databaseName,
            'database_username' => $databaseUsername,
            'error' => $exception->getMessage(),
            'state' => $this->provisioningState,
        ], $performedByUserId, $ipAddress, $userAgent);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logStep(
        string $action,
        ?int $tenantId,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'metadata' => $metadata,
        ]);
    }

    protected function resetState(): void
    {
        $this->provisioningState = [
            'database_created' => false,
            'database_user_created' => false,
            'tenant_record_created' => false,
            'connection_record_created' => false,
            'tenant_connected' => false,
            'local_school_created' => false,
            'database_name' => null,
            'database_username' => null,
        ];
    }

    protected function sanitizeIdentifier(string $value, int $maxLength): string
    {
        $sanitized = preg_replace('/[^A-Za-z0-9_]/', '_', $value) ?: 'tenant';

        return substr($sanitized, 0, $maxLength);
    }

    protected function escapeIdentifier(string $identifier): string
    {
        return str_replace('`', '``', $identifier);
    }

    protected function quoteSqlString(string $value): string
    {
        return DB::connection($this->platformConnection)->getPdo()->quote($value);
    }
}
