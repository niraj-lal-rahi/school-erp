<?php

namespace Tests\Feature\Platform;

use App\Models\Student;
use App\Models\User;
use App\Modules\SuperAdmin\Models\EmergencyAccessLog;
use App\Modules\SuperAdmin\Models\PlatformAdmin;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantDatabaseConnection;
use App\Modules\SuperAdmin\Resources\TenantDatabaseConnectionResource;
use App\Modules\SuperAdmin\Services\EmergencyAccessService;
use App\Modules\SuperAdmin\Services\ImpersonationService;
use App\Modules\SuperAdmin\Services\TenantDatabaseProvisioningService;
use App\Modules\SuperAdmin\Services\TenantEncryptionKeyService;
use App\Modules\Tenant\Services\TenantConnectionManager;
use App\Support\Auth\JwtManager;
use App\Support\Multitenancy\TenantContext as LegacyTenantContext;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

abstract class PlatformSecurityIsolationTestSuite extends TestCase
{
    use WithFaker;

    protected string $defaultDbPath;
    protected string $platformDbPath;
    protected string $tenantADbPath;
    protected string $tenantBDbPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareDatabasePaths();
        $this->configureConnections();
        $this->buildDefaultSchema();
        $this->buildPlatformSchema();
        $this->buildTenantSchemas();
        $this->registerTestRoutes();
    }

    protected function tearDown(): void
    {
        foreach ([
            $this->defaultDbPath,
            $this->platformDbPath,
            $this->tenantADbPath,
            $this->tenantBDbPath,
        ] as $path) {
            if (is_string($path) && $path !== '' && file_exists($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_superadmin_models_and_audit_use_platform_db_only(): void
    {
        $tenant = PlatformTenant::query()->create([
            'name' => 'Alpha School',
            'code' => 'alpha',
            'slug' => 'alpha',
            'status' => 'active',
        ]);

        app(\App\Modules\SuperAdmin\Services\PlatformAuditService::class)->tenantCreated(
            tenantId: $tenant->id,
            userId: null,
            metadata: ['source' => 'test']
        );

        $this->assertSame(1, DB::connection('platform')->table('platform_tenants')->count());
        $this->assertSame(1, DB::connection('platform')->table('platform_audit_logs')->count());

        Config::set('database.connections.tenant.database', $this->tenantADbPath);
        DB::purge('tenant');
        $this->assertFalse(Schema::connection('tenant')->hasTable('platform_tenants'));
        Config::set('database.connections.tenant.database', null);
        DB::purge('tenant');
    }

    public function test_tenant_a_cannot_access_tenant_b_database(): void
    {
        [$tenantA, $tenantB] = $this->seedPlatformTenantsWithConnections();
        $manager = app(TenantConnectionManager::class);

        $manager->connect($tenantA);
        $tenantASchoolName = DB::connection('tenant')->table('schools')->value('name');
        $manager->disconnect();

        $manager->connect($tenantB);
        $tenantBSchoolName = DB::connection('tenant')->table('schools')->value('name');
        $tenantBHasTenantARecord = DB::connection('tenant')->table('schools')->where('code', 'alpha')->exists();
        $manager->disconnect();

        $this->assertSame('Alpha School Local', $tenantASchoolName);
        $this->assertSame('Beta School Local', $tenantBSchoolName);
        $this->assertFalse($tenantBHasTenantARecord);
    }

    public function test_tenant_routes_fail_without_tenant_context(): void
    {
        $user = $this->createDefaultAuthUser();
        $headers = $this->headersFor($user);

        $this->withHeaders($headers)
            ->getJson('/api/v1/tenant-test/ping')
            ->assertNotFound();
    }

    public function test_suspended_tenant_is_blocked(): void
    {
        [$tenant] = $this->seedPlatformTenantsWithConnections(suspendFirst: true);
        $user = $this->createDefaultAuthUser();
        $headers = array_merge($this->headersFor($user), [
            'X-Tenant-Code' => $tenant->code,
        ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/tenant-test/ping')
            ->assertForbidden();
    }

    public function test_db_credentials_are_encrypted_at_rest(): void
    {
        $tenant = PlatformTenant::query()->create([
            'name' => 'Gamma School',
            'code' => 'gamma',
            'slug' => 'gamma',
            'status' => 'active',
        ]);

        $connection = TenantDatabaseConnection::query()->create([
            'tenant_id' => $tenant->id,
            'connection_name' => 'tenant',
            'database_name' => $this->tenantADbPath,
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'database_username' => 'tenant_user',
            'database_password' => 'super-secret-password',
            'database_driver' => 'sqlite',
            'is_active' => true,
            'connection_status' => 'connected',
        ]);

        $this->assertNotSame('127.0.0.1', $connection->getRawOriginal('database_host'));
        $this->assertNotSame('3306', $connection->getRawOriginal('database_port'));
        $this->assertNotSame('tenant_user', $connection->getRawOriginal('database_username'));
        $this->assertNotSame('super-secret-password', $connection->getRawOriginal('database_password'));
    }

    public function test_db_password_is_never_visible_in_api_resource(): void
    {
        $tenant = PlatformTenant::query()->create([
            'name' => 'Delta School',
            'code' => 'delta',
            'slug' => 'delta',
            'status' => 'active',
        ]);

        $connection = TenantDatabaseConnection::query()->create([
            'tenant_id' => $tenant->id,
            'connection_name' => 'tenant',
            'database_name' => $this->tenantADbPath,
            'database_host' => 'db.internal.local',
            'database_port' => '3306',
            'database_username' => 'dbadmin',
            'database_password' => 'never-show-me',
            'database_driver' => 'sqlite',
            'is_active' => true,
            'connection_status' => 'connected',
        ]);

        $payload = (new TenantDatabaseConnectionResource($connection))->resolve();

        $this->assertArrayHasKey('credentials', $payload);
        $this->assertSame('********', $payload['credentials']['password']);
        $this->assertArrayNotHasKey('database_password', $payload);
        $this->assertArrayNotHasKey('database_username', $payload);
    }

    public function test_tenant_provisioning_rollback_marks_tenant_failed_state(): void
    {
        $tenant = PlatformTenant::query()->create([
            'name' => 'Rollback School',
            'code' => 'rollback',
            'slug' => 'rollback',
            'status' => 'trial',
        ]);

        TenantDatabaseConnection::query()->create([
            'tenant_id' => $tenant->id,
            'connection_name' => 'tenant',
            'database_name' => $this->tenantADbPath,
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'database_username' => 'rollback_user',
            'database_password' => 'rollback-secret',
            'database_driver' => 'sqlite',
            'is_active' => true,
            'connection_status' => 'unknown',
        ]);

        $service = app(TenantDatabaseProvisioningService::class);

        $stateProperty = new ReflectionProperty($service, 'provisioningState');
        $stateProperty->setAccessible(true);
        $stateProperty->setValue($service, [
            'database_created' => false,
            'database_user_created' => false,
            'tenant_record_created' => true,
            'connection_record_created' => true,
            'tenant_connected' => false,
            'local_school_created' => false,
            'database_name' => null,
            'database_username' => null,
        ]);

        $method = (new ReflectionClass($service))->getMethod('rollbackProvisioning');
        $method->setAccessible(true);
        $method->invoke(
            $service,
            $tenant,
            null,
            null,
            null,
            '127.0.0.1',
            'PHPUnit',
            new RuntimeException('Provisioning failed in test.')
        );

        $tenant->refresh();
        $connection = TenantDatabaseConnection::query()->where('tenant_id', $tenant->id)->firstOrFail();

        $this->assertSame('suspended', $tenant->status);
        $this->assertFalse($connection->is_active);
        $this->assertSame('failed', $connection->connection_status);
    }

    public function test_impersonation_is_logged(): void
    {
        [$tenant] = $this->seedPlatformTenantsWithConnections();
        $platformAdmin = $this->createDefaultAuthUser();
        PlatformAdmin::query()->create([
            'user_id' => $platformAdmin->id,
            'admin_type' => 'super_admin',
            'status' => 'active',
        ]);

        $service = app(ImpersonationService::class);
        $payload = $service->start(
            tenant: $tenant,
            platformAdmin: $platformAdmin,
            reason: 'Investigating a tenant provisioning issue.',
            emergencyAccess: false,
            ttlMinutes: 15,
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit',
        );

        $this->assertArrayHasKey('access_token', $payload);
        $this->assertDatabaseHas('tenant_impersonation_logs', [
            'tenant_id' => $tenant->id,
            'platform_admin_user_id' => $platformAdmin->id,
            'status' => 'active',
        ], 'platform');
        $this->assertDatabaseHas('platform_audit_logs', [
            'tenant_id' => $tenant->id,
            'action' => 'tenant_impersonation_started',
        ], 'platform');
    }

    public function test_emergency_access_expires(): void
    {
        $tenant = PlatformTenant::query()->create([
            'name' => 'Expiry School',
            'code' => 'expiry',
            'slug' => 'expiry',
            'status' => 'active',
        ]);

        $admin = $this->createDefaultAuthUser();
        PlatformAdmin::query()->create([
            'user_id' => $admin->id,
            'admin_type' => 'security_admin',
            'status' => 'active',
        ]);

        DB::connection('platform')->table('tenant_security_settings')->insert([
            'tenant_id' => $tenant->id,
            'encryption_enabled' => true,
            'database_isolated' => true,
            'emergency_access_enabled' => true,
            'backup_encryption_enabled' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $log = EmergencyAccessLog::query()->create([
            'tenant_id' => $tenant->id,
            'platform_admin_user_id' => $admin->id,
            'approved_by_user_id' => $admin->id,
            'action' => 'approved',
            'reason' => 'Temporary emergency access.',
            'expires_at' => now()->subMinute(),
            'used_at' => null,
            'metadata' => [
                'requires_second_admin_approval' => false,
            ],
        ]);

        $validated = app(EmergencyAccessService::class)->validateApprovedAccess($log->id, $tenant->id, $admin->id);

        $this->assertNull($validated);
        $this->assertSame('expired', $log->fresh()->action);
    }

    public function test_encrypted_fields_are_unreadable_in_raw_db(): void
    {
        [$tenant] = $this->seedPlatformTenantsWithConnections();
        app(TenantEncryptionKeyService::class)->createInitialKey($tenant);

        app(TenantConnectionManager::class)->connect($tenant);
        app(LegacyTenantContext::class)->set(null);

        $student = new Student();
        $student->setConnection('tenant');
        $student->school_id = 1;
        $student->aadhaar_no = '1234-5678-9012';
        $student->medical_notes = 'Confidential note';
        $student->save();

        $raw = DB::connection('tenant')->table('students')->where('id', $student->id)->first();

        $this->assertNotSame('1234-5678-9012', $raw->aadhaar_no);
        $this->assertNotSame('Confidential note', $raw->medical_notes);

        $reloaded = (new Student())->setConnection('tenant')->newQueryWithoutScopes()->findOrFail($student->id);
        $this->assertSame('1234-5678-9012', $reloaded->aadhaar_no);
        $this->assertSame('Confidential note', $reloaded->medical_notes);

        app(TenantConnectionManager::class)->disconnect();
    }

    public function test_platform_db_does_not_contain_student_staff_or_fee_tables(): void
    {
        $this->assertFalse(Schema::connection('platform')->hasTable('students'));
        $this->assertFalse(Schema::connection('platform')->hasTable('staff'));
        $this->assertFalse(Schema::connection('platform')->hasTable('finance_fee_invoices'));
    }

    public function test_tenant_db_switching_does_not_leak_between_requests(): void
    {
        [$tenantA, $tenantB] = $this->seedPlatformTenantsWithConnections();
        $manager = app(TenantConnectionManager::class);

        $manager->connect($tenantA);
        $aName = DB::connection('tenant')->table('schools')->value('name');
        $manager->disconnect();

        $this->assertNull($manager->currentTenant());
        $this->assertSame('sqlite', config('database.connections.tenant.driver'));
        $this->assertNull(config('database.connections.tenant.database'));

        $manager->connect($tenantB);
        $bName = DB::connection('tenant')->table('schools')->value('name');
        $manager->disconnect();

        $this->assertSame('Alpha School Local', $aName);
        $this->assertSame('Beta School Local', $bName);
        $this->assertNull($manager->currentTenant());
    }

    protected function prepareDatabasePaths(): void
    {
        $base = storage_path('framework/testing');

        if (! is_dir($base)) {
            mkdir($base, 0777, true);
        }

        $suffix = uniqid('platform-security-', true);
        $this->defaultDbPath = $base.'/'.$suffix.'-default.sqlite';
        $this->platformDbPath = $base.'/'.$suffix.'-platform.sqlite';
        $this->tenantADbPath = $base.'/'.$suffix.'-tenant-a.sqlite';
        $this->tenantBDbPath = $base.'/'.$suffix.'-tenant-b.sqlite';

        foreach ([
            $this->defaultDbPath,
            $this->platformDbPath,
            $this->tenantADbPath,
            $this->tenantBDbPath,
        ] as $path) {
            if (! file_exists($path)) {
                touch($path);
            }
        }
    }

    protected function configureConnections(): void
    {
        $sqliteConfig = static fn (string $path): array => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ];

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite', $sqliteConfig($this->defaultDbPath));
        Config::set('database.connections.platform', $sqliteConfig($this->platformDbPath));
        Config::set('database.connections.tenant', $sqliteConfig(''));

        DB::purge('sqlite');
        DB::purge('platform');
        DB::purge('tenant');

        App::forgetInstance(TenantConnectionManager::class);
        App::forgetInstance(LegacyTenantContext::class);
    }

    protected function buildDefaultSchema(): void
    {
        Schema::connection('sqlite')->dropAllTables();

        Schema::connection('sqlite')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('uuid')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->string('status')->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('sqlite')->create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });
    }

    protected function buildPlatformSchema(): void
    {
        Schema::connection('platform')->dropAllTables();

        Schema::connection('platform')->create('platform_tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('status');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('platform')->create('tenant_database_connections', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('connection_name');
            $table->string('database_name');
            $table->text('database_host')->nullable();
            $table->text('database_port')->nullable();
            $table->text('database_username')->nullable();
            $table->text('database_password')->nullable();
            $table->string('database_driver')->default('sqlite');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->string('connection_status')->default('unknown');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::connection('platform')->create('platform_admins', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('admin_type');
            $table->string('status');
            $table->timestamps();
        });

        Schema::connection('platform')->create('platform_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection('platform')->create('tenant_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->boolean('encryption_enabled')->default(true);
            $table->boolean('database_isolated')->default(true);
            $table->boolean('emergency_access_enabled')->default(false);
            $table->boolean('backup_encryption_enabled')->default(true);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::connection('platform')->create('tenant_impersonation_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('platform_admin_user_id');
            $table->unsignedBigInteger('impersonated_user_id');
            $table->string('status');
            $table->text('reason');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection('platform')->create('emergency_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('platform_admin_user_id');
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->string('action');
            $table->text('reason');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::connection('platform')->create('tenant_encryption_keys', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('key_reference');
            $table->text('encrypted_data_key');
            $table->unsignedInteger('key_version');
            $table->string('status');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();
        });

        Schema::connection('platform')->create('tenant_key_rotation_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedInteger('old_key_version');
            $table->unsignedInteger('new_key_version');
            $table->unsignedBigInteger('rotated_by')->nullable();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::connection('platform')->create('tenant_backup_logs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('backup_type');
            $table->string('storage_disk')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('checksum')->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    protected function buildTenantSchemas(): void
    {
        $this->buildTenantSchemaFor($this->tenantADbPath, 'Alpha School Local', 'alpha', 'alpha', 1);
        $this->buildTenantSchemaFor($this->tenantBDbPath, 'Beta School Local', 'beta', 'beta', 2);

        Config::set('database.connections.tenant.database', null);
        DB::purge('tenant');
        App::forgetInstance(TenantConnectionManager::class);
    }

    protected function buildTenantSchemaFor(string $databasePath, string $schoolName, string $code, string $slug, int $schoolId): void
    {
        Config::set('database.connections.tenant.database', $databasePath);
        DB::purge('tenant');
        Schema::connection('tenant')->dropAllTables();

        Schema::connection('tenant')->create('schools', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('code')->nullable();
            $table->string('slug')->nullable();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::connection('tenant')->create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
        });

        Schema::connection('tenant')->create('students', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->text('aadhaar_no')->nullable();
            $table->text('medical_notes')->nullable();
            $table->timestamps();
        });

        DB::connection('tenant')->table('schools')->insert([
            'id' => $schoolId,
            'name' => $schoolName,
            'code' => $code,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminRoleId = DB::connection('tenant')->table('roles')->insertGetId([
            'school_id' => $schoolId,
            'code' => 'tenant_admin',
            'slug' => 'tenant_admin',
            'name' => 'Tenant Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminUserId = DB::connection('tenant')->table('users')->insertGetId([
            'school_id' => $schoolId,
            'name' => $schoolName.' Admin',
            'first_name' => $schoolName,
            'last_name' => 'Admin',
            'email' => $code.'@school.test',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::connection('tenant')->table('user_roles')->insert([
            'school_id' => $schoolId,
            'user_id' => $adminUserId,
            'role_id' => $adminRoleId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function registerTestRoutes(): void
    {
        Route::middleware(['auth:api', 'resolve.tenant', 'switch.tenant.database', 'tenant.active'])
            ->get('/api/v1/tenant-test/ping', fn () => response()->json(['ok' => true]));
    }

    /**
     * @return array{0:PlatformTenant,1:PlatformTenant}
     */
    protected function seedPlatformTenantsWithConnections(bool $suspendFirst = false): array
    {
        $tenantA = PlatformTenant::query()->create([
            'name' => 'Alpha School',
            'code' => 'alpha',
            'slug' => 'alpha',
            'status' => $suspendFirst ? 'suspended' : 'active',
            'suspended_at' => $suspendFirst ? now() : null,
        ]);

        $tenantB = PlatformTenant::query()->create([
            'name' => 'Beta School',
            'code' => 'beta',
            'slug' => 'beta',
            'status' => 'active',
        ]);

        foreach ([[$tenantA, $this->tenantADbPath], [$tenantB, $this->tenantBDbPath]] as [$tenant, $path]) {
            TenantDatabaseConnection::query()->create([
                'tenant_id' => $tenant->id,
                'connection_name' => 'tenant',
                'database_name' => $path,
                'database_host' => 'localhost',
                'database_port' => '0',
                'database_username' => 'tenant_user',
                'database_password' => 'tenant_password',
                'database_driver' => 'sqlite',
                'is_active' => true,
                'connection_status' => 'connected',
            ]);

            DB::connection('platform')->table('tenant_security_settings')->insert([
                'tenant_id' => $tenant->id,
                'encryption_enabled' => true,
                'database_isolated' => true,
                'emergency_access_enabled' => true,
                'backup_encryption_enabled' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return [$tenantA, $tenantB];
    }

    protected function createDefaultAuthUser(): User
    {
        return User::query()->create([
            'uuid' => 'test-user-uuid',
            'school_id' => null,
            'first_name' => 'Platform',
            'last_name' => 'Admin',
            'name' => 'Platform Admin',
            'email' => 'platform@example.test',
            'phone' => '9999999999',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function headersFor(User $user): array
    {
        app('auth')->forgetGuards();

        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];
    }
}
