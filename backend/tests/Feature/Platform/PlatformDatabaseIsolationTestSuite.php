<?php

namespace Tests\Feature\Platform;

use App\Models\Platform\PlatformAdmin;
use App\Models\Platform\PlatformAuditLog;
use App\Models\Platform\PlatformTenant;
use App\Models\Platform\TenantDatabaseConnection;
use App\Models\Platform\TenantSecuritySetting;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use App\Services\Platform\TenantConnectionManager;
use App\Http\Middleware\SwitchTenantDatabase;
use App\Support\Auth\JwtManager;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class PlatformDatabaseIsolationTestSuite extends TestCase
{
    use RefreshDatabase;

    protected static bool $routesRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        $defaultConnectionName = Config::get('database.default');
        $defaultConnection = Config::get("database.connections.{$defaultConnectionName}", []);
        Config::set('database.connections.platform', $defaultConnection);
        $this->ensurePlatformSchema();

        if (! self::$routesRegistered) {
            $this->registerPlatformTestRoutes();
            self::$routesRegistered = true;
        }
    }

    protected function ensurePlatformSchema(): void
    {
        if (! Schema::connection('platform')->hasTable('platform_tenants')) {
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
        }

        if (! Schema::connection('platform')->hasTable('tenant_database_connections')) {
            Schema::connection('platform')->create('tenant_database_connections', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('connection_name');
                $table->string('database_name');
                $table->text('database_host');
                $table->text('database_port');
                $table->text('database_username');
                $table->text('database_password');
                $table->string('database_driver')->default('mysql');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_connected_at')->nullable();
                $table->string('connection_status')->default('unknown');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::connection('platform')->hasTable('platform_admins')) {
            Schema::connection('platform')->create('platform_admins', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('admin_type');
                $table->string('status');
                $table->timestamps();
            });
        }

        if (! Schema::connection('platform')->hasTable('platform_audit_logs')) {
            Schema::connection('platform')->create('platform_audit_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action');
                $table->string('module')->nullable();
                $table->text('description')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::connection('platform')->hasTable('tenant_security_settings')) {
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
        }
    }

    protected function registerPlatformTestRoutes(): void
    {
        Route::middleware(['auth:api', 'ensure.platform.admin'])
            ->get('/api/v1/test/platform-access', function () {
                return response()->json([
                    'platform_tenant_count' => PlatformTenant::query()->count(),
                ]);
            });

        Route::middleware(['platform.tenant.resolve'])
            ->get('/api/v1/test/platform-tenant-resolution', fn () => response()->json(['ok' => true]));

        Route::middleware(['platform.tenant.resolve', 'switch.tenant.database'])
            ->get('/api/v1/test/platform-tenant-switch', fn () => response()->json(['ok' => true]));
    }

    protected function platformHeadersFor(User $user): array
    {
        app('auth')->forgetGuards();

        return [
            'Authorization' => 'Bearer '.app(JwtManager::class)->issueAccessToken($user),
        ];
    }

    protected function ensureSeeded(): void
    {
        if (! School::withoutGlobalScopes()->where('code', 'greenwood')->exists()) {
            $this->seed();
        }
    }

    protected function createSuperAdminUser(): User
    {
        $this->ensureSeeded();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $role = Role::withoutGlobalScopes()->whereNull('school_id')->where('code', 'super_admin')->firstOrFail();

        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => $user->school_id],
        ]);

        return $user->refresh();
    }

    protected function createPlatformTenant(string $code = 'school_001', string $status = 'active'): PlatformTenant
    {
        return PlatformTenant::query()->create([
            'name' => 'Tenant '.Str::upper($code),
            'code' => $code,
            'slug' => Str::slug($code),
            'email' => $code.'@example.test',
            'phone' => '9000000000',
            'status' => $status,
            'trial_ends_at' => now()->addDays(14),
            'activated_at' => $status === 'active' ? now() : null,
            'suspended_at' => $status === 'suspended' ? now() : null,
        ]);
    }

    protected function createActiveConnection(PlatformTenant $tenant, array $overrides = []): TenantDatabaseConnection
    {
        $platformConnection = Config::get('database.connections.platform');
        $defaultConnectionName = Config::get('database.default');
        $defaultConnection = Config::get("database.connections.{$defaultConnectionName}", []);

        return TenantDatabaseConnection::query()->create(array_merge([
            'tenant_id' => $tenant->id,
            'connection_name' => 'tenant_'.$tenant->id,
            'database_name' => (string) ($platformConnection['database'] ?? $defaultConnection['database'] ?? 'laravel'),
            'database_host' => (string) ($platformConnection['host'] ?? $defaultConnection['host'] ?? '127.0.0.1'),
            'database_port' => (string) ($platformConnection['port'] ?? $defaultConnection['port'] ?? '3306'),
            'database_username' => (string) ($platformConnection['username'] ?? $defaultConnection['username'] ?? 'root'),
            'database_password' => (string) ($platformConnection['password'] ?? $defaultConnection['password'] ?? ''),
            'database_driver' => (string) ($platformConnection['driver'] ?? $defaultConnection['driver'] ?? 'mysql'),
            'is_active' => true,
            'connection_status' => 'unknown',
        ], $overrides));
    }

    public function test_super_admin_can_access_platform_database(): void
    {
        $user = $this->createSuperAdminUser();
        $this->createPlatformTenant('school_001');

        $this->withHeaders($this->platformHeadersFor($user))
            ->getJson('/api/v1/test/platform-access')
            ->assertOk()
            ->assertJsonPath('platform_tenant_count', 1);
    }

    public function test_tenant_a_connects_only_to_tenant_a_database_configuration(): void
    {
        $tenantA = $this->createPlatformTenant('school_001');
        $this->createActiveConnection($tenantA, [
            'database_name' => 'erp_school_001',
        ]);

        $configured = app(TenantConnectionManager::class)->configureTenantConnection($tenantA->fresh()->load('databaseConnection'));

        $this->assertSame('tenant_'.$tenantA->id, $configured['connection_name']);
        $this->assertSame('erp_school_001', $configured['config']['database']);
    }

    public function test_tenant_b_cannot_reuse_tenant_a_database_configuration(): void
    {
        $tenantA = $this->createPlatformTenant('school_001');
        $tenantB = $this->createPlatformTenant('school_002');
        $this->createActiveConnection($tenantA, ['database_name' => 'erp_school_001']);
        $this->createActiveConnection($tenantB, ['database_name' => 'erp_school_002']);

        $manager = app(TenantConnectionManager::class);
        $configA = $manager->configureTenantConnection($tenantA->fresh()->load('databaseConnection'));
        $configB = $manager->configureTenantConnection($tenantB->fresh()->load('databaseConnection'));

        $this->assertSame('erp_school_001', $configA['config']['database']);
        $this->assertSame('erp_school_002', $configB['config']['database']);
        $this->assertNotSame($configA['config']['database'], $configB['config']['database']);
    }

    public function test_suspended_tenant_is_blocked(): void
    {
        $tenant = $this->createPlatformTenant('school_003', 'suspended');
        $user = User::factory()->create();
        $request = Request::create('/api/v1/test/platform-tenant-switch', 'GET');
        $request->setUserResolver(fn () => $user);
        $request->attributes->set('platformTenant', $tenant);

        try {
            app(SwitchTenantDatabase::class)->handle($request, fn () => response()->json(['ok' => true]));
            $this->fail('Suspended tenants should be blocked before DB switching.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_missing_tenant_returns_proper_error(): void
    {
        $user = User::factory()->create();

        $this->withHeaders($this->platformHeadersFor($user))
            ->getJson('/api/v1/test/platform-tenant-resolution')
            ->assertNotFound();
    }

    public function test_invalid_db_credentials_log_failure(): void
    {
        $tenant = $this->createPlatformTenant('school_004');
        $this->createActiveConnection($tenant, [
            'database_driver' => 'invalid-driver',
            'database_username' => 'invalid_user',
            'database_password' => 'invalid_password',
        ]);

        $result = app(TenantConnectionManager::class)->testConnection($tenant->fresh()->load('databaseConnection'));

        $this->assertFalse($result);

        $audit = PlatformAuditLog::query()
            ->where('tenant_id', $tenant->id)
            ->where('action', 'tenant.database.connection_tested')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertFalse((bool) ($audit->metadata['success'] ?? true));
    }

    public function test_tenant_database_credentials_are_encrypted(): void
    {
        $tenant = $this->createPlatformTenant('school_005');
        $record = $this->createActiveConnection($tenant, [
            'database_host' => '127.0.0.1',
            'database_port' => '3306',
            'database_username' => 'tenant_user',
            'database_password' => 'tenant_secret_password',
        ]);

        $raw = \DB::connection('platform')
            ->table('tenant_database_connections')
            ->where('id', $record->id)
            ->first();

        $this->assertNotSame('127.0.0.1', $raw->database_host);
        $this->assertNotSame('3306', $raw->database_port);
        $this->assertNotSame('tenant_user', $raw->database_username);
        $this->assertNotSame('tenant_secret_password', $raw->database_password);
    }

    public function test_platform_database_does_not_store_student_domain_models_on_platform_connection(): void
    {
        $platformTenant = new PlatformTenant();
        $student = new Student();

        $this->assertSame('platform', $platformTenant->getConnectionName());
        $this->assertNotSame('platform', $student->getConnectionName());
    }

    public function test_tenant_db_switching_does_not_leak_between_requests(): void
    {
        $tenantA = $this->createPlatformTenant('school_006');
        $tenantB = $this->createPlatformTenant('school_007');
        $this->createActiveConnection($tenantA);
        $this->createActiveConnection($tenantB);

        $manager = app(TenantConnectionManager::class);
        $originalDefault = Config::get('database.default');

        $manager->connect($tenantA->fresh()->load('databaseConnection'));
        $this->assertSame('tenant_'.$tenantA->id, $manager->currentTenantConnectionName());
        $this->assertSame('tenant_'.$tenantA->id, Config::get('database.default'));
        $manager->disconnect();

        $manager->connect($tenantB->fresh()->load('databaseConnection'));
        $this->assertSame('tenant_'.$tenantB->id, $manager->currentTenantConnectionName());
        $this->assertSame('tenant_'.$tenantB->id, Config::get('database.default'));
        $manager->disconnect();

        $this->assertSame($originalDefault, Config::get('database.default'));
        $this->assertNull($manager->currentTenantConnectionName());
    }
}
