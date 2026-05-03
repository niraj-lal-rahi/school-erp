<?php

namespace App\Services\Platform;

use App\Models\AcademicYear;
use App\Models\Permission;
use App\Models\Platform\PlatformTenant;
use App\Models\Platform\TenantDatabaseConnection;
use App\Models\Role;
use App\Models\School;
use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Settings\DefaultBrandingSeeder;
use Database\Seeders\Settings\DefaultLocalizationSeeder;
use Database\Seeders\Settings\DefaultSecuritySettingSeeder;
use Database\Seeders\Settings\DefaultSettingSeeder;
use Database\Seeders\Settings\FeatureFlagSeeder;
use Database\Seeders\Settings\SettingGroupSeeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TenantDatabaseProvisioningService
{
    public function __construct(
        protected TenantConnectionManager $connections,
        protected PlatformAuditService $audit,
    ) {
    }

    public function provisionDatabase(PlatformTenant $tenant): array
    {
        $databaseName = $this->buildDatabaseName($tenant);
        $username = $this->resolveDatabaseUsername($tenant);
        $password = $this->resolveDatabasePassword();
        $host = (string) Config::get('database.connections.platform.host', env('PLATFORM_DB_HOST', env('DB_HOST', '127.0.0.1')));
        $port = (string) Config::get('database.connections.platform.port', env('PLATFORM_DB_PORT', env('DB_PORT', '3306')));
        $driver = (string) Config::get('database.connections.platform.driver', env('PLATFORM_DB_DRIVER', 'mysql'));
        $record = null;
        $databaseCreated = false;
        $databaseUserCreated = false;

        try {
            if (! $this->databaseExists($databaseName)) {
                $this->createDatabase($databaseName);
                $databaseCreated = true;
            }

            if ($this->shouldCreateDedicatedDatabaseUser()) {
                if (! $this->databaseUserExists($username)) {
                    $this->createDatabaseUser($username, $password);
                    $databaseUserCreated = true;
                }

                $this->grantDatabasePrivileges($databaseName, $username);
            } else {
                $username = (string) Config::get('database.connections.platform.username', env('PLATFORM_DB_USERNAME', env('DB_USERNAME', 'root')));
                $password = (string) Config::get('database.connections.platform.password', env('PLATFORM_DB_PASSWORD', env('DB_PASSWORD', '')));
            }

            $record = $this->storeConnectionDetails(
                $tenant,
                [
                    'connection_name' => $this->connectionName($tenant),
                    'database_name' => $databaseName,
                    'database_host' => $host,
                    'database_port' => $port,
                    'database_username' => $username,
                    'database_password' => $password,
                    'database_driver' => $driver,
                ],
            );

            $this->connections->connect($tenant->fresh()->load('databaseConnection'));

            try {
                $this->runTenantMigrations($tenant);
                $this->seedTenantDefaults($tenant);
            } finally {
                $this->connections->disconnect();
            }

            $tested = $this->connections->testConnection($tenant->fresh()->load('databaseConnection'));

            $this->updatePlatformConnectionStatus($record->id, $tested ? 'connected' : 'failed');

            if (! $tested) {
                throw new RuntimeException('Tenant database provisioning completed, but the connection test failed.');
            }

            $this->audit->logTenantDatabaseCreated($tenant, [
                'database_name' => $databaseName,
                'connection_name' => $record->connection_name,
                'database_user_created' => $databaseUserCreated,
                'database_isolated' => true,
            ]);

            return [
                'tenant_id' => $tenant->id,
                'database_name' => $databaseName,
                'connection_name' => $record->connection_name,
                'database_user_created' => $databaseUserCreated,
                'status' => 'provisioned',
            ];
        } catch (Throwable $exception) {
            $this->rollbackProvisioning($tenant, [
                'database_name' => $databaseName,
                'database_username' => $username,
                'record_id' => $record?->id,
                'drop_database' => $databaseCreated,
                'drop_database_user' => $databaseUserCreated,
            ]);

            throw $exception;
        }
    }

    public function runTenantMigrations(PlatformTenant $tenant): void
    {
        foreach ($this->tenantMigrationPaths() as $migrationPath) {
            $exitCode = Artisan::call('migrate', [
                '--database' => $this->connectionName($tenant),
                '--path' => $migrationPath,
                '--realpath' => true,
                '--force' => true,
            ]);

            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'Tenant migration failed for "%s": %s',
                    basename($migrationPath),
                    trim(Artisan::output()),
                ));
            }
        }
    }

    public function seedTenantDefaults(PlatformTenant $tenant): void
    {
        $school = $this->createOrUpdateLocalSchool($tenant);
        $this->createOrUpdateAcademicYear($school);

        foreach ($this->tenantSeederClasses() as $seederClass) {
            App::make($seederClass)->run();
        }

        $this->seedTenantRoles($school);
    }

    public function rollbackProvisioning(PlatformTenant $tenant, array $context = []): void
    {
        $this->connections->disconnect();

        $databaseName = $context['database_name'] ?? $tenant->databaseConnection?->database_name;
        $databaseUsername = $context['database_username'] ?? $tenant->databaseConnection?->database_username;
        $recordId = $context['record_id'] ?? null;

        if ($recordId) {
            DB::connection('platform')
                ->table('tenant_database_connections')
                ->where('id', $recordId)
                ->update([
                    'is_active' => false,
                    'connection_status' => 'failed',
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        if (($context['drop_database'] ?? false) && $databaseName) {
            DB::connection('platform')->unprepared(sprintf(
                'DROP DATABASE IF EXISTS `%s`',
                $this->sanitizeIdentifier($databaseName),
            ));
        }

        if (($context['drop_database_user'] ?? false) && $databaseUsername) {
            DB::connection('platform')->unprepared(sprintf(
                "DROP USER IF EXISTS '%s'@'%%'",
                $this->escapeSqlLiteral($this->sanitizeDatabaseUsername($databaseUsername)),
            ));
        }

        $this->audit->log(
            'tenant.database.rollback',
            'database',
            'Tenant database provisioning rollback executed.',
            $tenant,
            null,
            [
                'database_name' => $databaseName,
                'database_user' => $databaseUsername,
                'drop_database' => (bool) ($context['drop_database'] ?? false),
                'drop_database_user' => (bool) ($context['drop_database_user'] ?? false),
            ],
        );
    }

    protected function createDatabase(string $databaseName): void
    {
        DB::connection('platform')->unprepared(sprintf(
            'CREATE DATABASE `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $this->sanitizeIdentifier($databaseName),
        ));
    }

    protected function createDatabaseUser(string $username, string $password): void
    {
        $safeUsername = $this->escapeSqlLiteral($this->sanitizeDatabaseUsername($username));
        $safePassword = $this->escapeSqlLiteral($password);

        DB::connection('platform')->unprepared(
            "CREATE USER '{$safeUsername}'@'%' IDENTIFIED BY '{$safePassword}'"
        );
    }

    protected function grantDatabasePrivileges(string $databaseName, string $username): void
    {
        $safeDatabase = $this->sanitizeIdentifier($databaseName);
        $safeUsername = $this->escapeSqlLiteral($this->sanitizeDatabaseUsername($username));

        DB::connection('platform')->unprepared(
            "GRANT ALL PRIVILEGES ON `{$safeDatabase}`.* TO '{$safeUsername}'@'%'"
        );
        DB::connection('platform')->unprepared('FLUSH PRIVILEGES');
    }

    protected function databaseExists(string $databaseName): bool
    {
        return DB::connection('platform')
            ->table('information_schema.schemata')
            ->where('schema_name', $databaseName)
            ->exists();
    }

    protected function databaseUserExists(string $username): bool
    {
        return DB::connection('platform')
            ->table('mysql.user')
            ->where('user', $username)
            ->exists();
    }

    protected function storeConnectionDetails(PlatformTenant $tenant, array $attributes): TenantDatabaseConnection
    {
        DB::connection('platform')
            ->table('tenant_database_connections')
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        $record = $this->platformConnectionModel()->newQuery()
            ->withTrashed()
            ->where('tenant_id', $tenant->id)
            ->where('connection_name', $attributes['connection_name'])
            ->first();

        if (! $record) {
            $record = $this->platformConnectionModel();
            $record->tenant_id = $tenant->id;
            $record->connection_name = $attributes['connection_name'];
        }

        $record->fill([
            'database_name' => $attributes['database_name'],
            'database_host' => $attributes['database_host'],
            'database_port' => $attributes['database_port'],
            'database_username' => $attributes['database_username'],
            'database_password' => $attributes['database_password'],
            'database_driver' => $attributes['database_driver'],
            'is_active' => true,
            'connection_status' => 'unknown',
            'deleted_at' => null,
        ]);

        $record->save();

        return $record;
    }

    protected function createOrUpdateLocalSchool(PlatformTenant $tenant): School
    {
        $school = School::withoutGlobalScopes()->find($tenant->id) ?? new School();

        if (! $school->exists) {
            $school->forceFill(['id' => $tenant->id]);
        }

        $school->forceFill([
            'uuid' => $school->uuid ?: (string) Str::uuid(),
            'name' => $tenant->name,
            'code' => $tenant->code,
            'slug' => $tenant->slug,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
            'domain' => $school->domain ?: sprintf('%s.local', $tenant->slug),
            'subdomain' => $school->subdomain ?: $tenant->slug,
            'timezone' => $school->timezone ?: 'Asia/Kolkata',
            'currency' => $school->currency ?: 'INR',
            'locale' => $school->locale ?: 'en',
            'status' => $tenant->status === 'active' ? 'active' : 'trial',
            'trial_ends_at' => $tenant->trial_ends_at,
            'activated_at' => $tenant->activated_at,
            'suspended_at' => $tenant->suspended_at,
            'settings' => array_merge([
                'platform_tenant_id' => $tenant->id,
                'database_isolated' => true,
            ], (array) ($school->settings ?? [])),
            'storage_disk' => $school->storage_disk ?: 'private',
        ]);

        $school->save();

        return $school->refresh();
    }

    protected function createOrUpdateAcademicYear(School $school): AcademicYear
    {
        $year = now()->month >= 4 ? now()->year : now()->subYear()->year;
        $nextYear = $year + 1;

        AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'is_active' => false,
                'updated_at' => now(),
            ]);

        return AcademicYear::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => sprintf('AY-%d-%02d', $year, $nextYear % 100),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => sprintf('Academic Year %d-%d', $year, $nextYear),
                'start_date' => sprintf('%d-04-01', $year),
                'end_date' => sprintf('%d-03-31', $nextYear),
                'is_active' => true,
                'is_current' => true,
                'status' => 'active',
            ],
        );
    }

    protected function seedTenantRoles(School $school): void
    {
        $permissionIds = Permission::query()->pluck('id', 'code');

        $roles = [
            [
                'name' => 'Tenant Administrator',
                'code' => 'tenant_admin',
                'slug' => 'school-admin',
                'description' => 'Tenant administrator with full school access.',
                'is_default' => true,
                'permission_codes' => $permissionIds->keys()->all(),
            ],
            [
                'name' => 'Principal',
                'code' => 'principal',
                'slug' => 'principal',
                'description' => 'School principal with academic and reporting access.',
                'is_default' => false,
                'permission_codes' => [
                    'academic-management.view',
                    'academic-management.manage',
                    'students.view',
                    'attendance.view',
                    'attendance.manage',
                    'communication.view',
                    'communication.manage',
                    'exams.view',
                    'exams.manage',
                    'reports.view',
                    'reports.run',
                    'reports.export',
                    'portal.view',
                    'workflows.view',
                    'workflows.approve',
                    'documents.view',
                    'documents.manage',
                    'documents.verify',
                    'settings.view',
                ],
            ],
            [
                'name' => 'Teacher',
                'code' => 'teacher',
                'slug' => 'teacher',
                'description' => 'Teacher role for academic, attendance, communication, and exam workflows.',
                'is_default' => false,
                'permission_codes' => [
                    'academic-management.view',
                    'academic-management.manage',
                    'students.view',
                    'attendance.view',
                    'attendance.manage',
                    'timetable.view',
                    'communication.view',
                    'communication.manage',
                    'exams.view',
                    'exams.manage',
                    'portal.view',
                    'documents.view',
                ],
            ],
            [
                'name' => 'Accountant',
                'code' => 'accountant',
                'slug' => 'accountant',
                'description' => 'Finance-focused role for fees and reporting.',
                'is_default' => false,
                'permission_codes' => [
                    'students.view',
                    'finance.view',
                    'finance.manage',
                    'communication.view',
                    'reports.view',
                    'reports.run',
                    'reports.export',
                    'workflows.view',
                    'workflows.approve',
                    'documents.view',
                    'documents.verify',
                    'settings.view',
                ],
            ],
            [
                'name' => 'Receptionist',
                'code' => 'receptionist',
                'slug' => 'receptionist',
                'description' => 'Front-desk role for student and communication workflows.',
                'is_default' => false,
                'permission_codes' => [
                    'students.view',
                    'students.create',
                    'students.update',
                    'students.documents.upload',
                    'communication.view',
                    'communication.manage',
                    'transport.view',
                    'portal.view',
                    'documents.view',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'slug' => $roleData['slug'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $roleData['name'],
                    'code' => $roleData['code'],
                    'slug' => $roleData['slug'],
                    'scope' => 'tenant',
                    'description' => $roleData['description'],
                    'role_type' => 'tenant',
                    'is_default' => $roleData['is_default'],
                    'status' => 'active',
                ],
            );

            $syncIds = collect($roleData['permission_codes'])
                ->map(fn (string $code) => $permissionIds->get($code))
                ->filter()
                ->values()
                ->all();

            $role->permissions()->syncWithPivotValues($syncIds, [
                'school_id' => $school->id,
            ]);
        }
    }

    protected function updatePlatformConnectionStatus(int $recordId, string $status): void
    {
        DB::connection('platform')
            ->table('tenant_database_connections')
            ->where('id', $recordId)
            ->update([
                'connection_status' => $status,
                'updated_at' => now(),
            ]);
    }

    protected function tenantMigrationPaths(): array
    {
        $skip = [
            '0001_01_01_000001_create_cache_table.php',
            '0001_01_01_000002_create_jobs_table.php',
            '2026_04_30_030000_create_saas_enhancement_tables.php',
            '2026_05_04_000000_create_platform_database_separation_tables.php',
        ];

        return collect(glob(database_path('migrations/*.php')) ?: [])
            ->reject(fn (string $path) => in_array(basename($path), $skip, true))
            ->values()
            ->all();
    }

    protected function tenantSeederClasses(): array
    {
        return [
            PermissionSeeder::class,
            SettingGroupSeeder::class,
            DefaultSettingSeeder::class,
            FeatureFlagSeeder::class,
            DefaultBrandingSeeder::class,
            DefaultLocalizationSeeder::class,
            DefaultSecuritySettingSeeder::class,
        ];
    }

    protected function buildDatabaseName(PlatformTenant $tenant): string
    {
        $prefix = (string) env('TENANT_DB_DATABASE_PREFIX', 'erp_');
        $candidate = $tenant->code ?: $tenant->slug ?: ('tenant_'.$tenant->id);
        $normalized = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', $candidate) ?: 'tenant_'.$tenant->id);
        $normalized = trim($normalized, '_');

        return $this->sanitizeIdentifier($prefix.$normalized);
    }

    protected function resolveDatabaseUsername(PlatformTenant $tenant): string
    {
        if (! $this->shouldCreateDedicatedDatabaseUser()) {
            return (string) Config::get('database.connections.platform.username', env('PLATFORM_DB_USERNAME', env('DB_USERNAME', 'root')));
        }

        $prefix = (string) env('TENANT_DB_USERNAME_PREFIX', 'tenant_');
        $base = strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', $tenant->code ?: $tenant->slug ?: (string) $tenant->id) ?: 'tenant_'.$tenant->id);
        $username = $prefix.$base;

        return substr($this->sanitizeDatabaseUsername($username), 0, 32);
    }

    protected function resolveDatabasePassword(): string
    {
        if (! $this->shouldCreateDedicatedDatabaseUser()) {
            return (string) Config::get('database.connections.platform.password', env('PLATFORM_DB_PASSWORD', env('DB_PASSWORD', '')));
        }

        return (string) Str::password(
            max(16, (int) env('TENANT_DB_PASSWORD_LENGTH', 24)),
            true,
            true,
            true,
            false,
        );
    }

    protected function shouldCreateDedicatedDatabaseUser(): bool
    {
        return filter_var(env('TENANT_DB_PROVISION_CREATE_USER', false), FILTER_VALIDATE_BOOL);
    }

    protected function connectionName(PlatformTenant $tenant): string
    {
        return 'tenant_'.$tenant->id;
    }

    protected function sanitizeIdentifier(string $value): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_]+/', '_', trim($value)) ?: '';

        if ($clean === '') {
            throw new RuntimeException('Invalid database identifier provided for tenant provisioning.');
        }

        return $clean;
    }

    protected function sanitizeDatabaseUsername(string $value): string
    {
        $clean = preg_replace('/[^A-Za-z0-9_]+/', '_', trim($value)) ?: '';

        if ($clean === '') {
            throw new RuntimeException('Invalid database username provided for tenant provisioning.');
        }

        return $clean;
    }

    protected function escapeSqlLiteral(string $value): string
    {
        return str_replace("'", "''", $value);
    }

    protected function platformConnectionModel(): TenantDatabaseConnection
    {
        $model = new TenantDatabaseConnection();
        $model->setConnection('platform');

        return $model;
    }
}
