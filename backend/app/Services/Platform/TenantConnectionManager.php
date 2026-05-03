<?php

namespace App\Services\Platform;

use App\Models\Platform\PlatformTenant;
use App\Models\Platform\TenantDatabaseConnection;
use App\Models\School;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class TenantConnectionManager
{
    protected ?string $activeConnectionName = null;
    protected ?string $originalDefaultConnection = null;

    public function __construct(
        protected TenantContext $tenantContext,
        protected PlatformAuditService $audit,
    ) {
    }

    public function resolveTenant(Request $request): ?PlatformTenant
    {
        $headerCode = $request->header('X-Tenant-Code');
        $routeTenant = $request->route('tenant');
        $host = (string) $request->getHost();
        $subdomain = $this->extractSubdomain($host);

        $tenant = PlatformTenant::query()
            ->when($headerCode, fn ($query) => $query->orWhere('code', $headerCode)->orWhere('slug', $headerCode))
            ->when(is_string($routeTenant) && $routeTenant !== '', fn ($query) => $query->orWhere('code', $routeTenant)->orWhere('slug', $routeTenant))
            ->when($subdomain, fn ($query) => $query->orWhere('slug', $subdomain)->orWhere('code', $subdomain))
            ->first();

        if ($tenant) {
            return $tenant;
        }

        $school = School::withoutGlobalScopes()
            ->when($headerCode, fn ($query) => $query->orWhere('code', $headerCode)->orWhere('slug', $headerCode))
            ->when(is_string($routeTenant) && $routeTenant !== '', fn ($query) => $query->orWhere('code', $routeTenant)->orWhere('slug', $routeTenant))
            ->when($host !== '', fn ($query) => $query->orWhere('domain', $host)->orWhere('subdomain', $subdomain))
            ->first();

        if (! $school) {
            return null;
        }

        return PlatformTenant::query()
            ->where('code', $school->code)
            ->orWhere('slug', $school->slug)
            ->first();
    }

    public function configureTenantConnection(PlatformTenant $tenant): array
    {
        $record = $this->activeDatabaseConnectionFor($tenant);
        $connectionName = $record->connection_name ?: 'tenant';

        $base = Config::get('database.connections.tenant')
            ?: Config::get('database.connections.mysql')
            ?: [];

        $connectionConfig = array_merge($base, [
            'driver' => $record->database_driver ?: ($base['driver'] ?? 'mysql'),
            'host' => $record->database_host,
            'port' => $record->database_port,
            'database' => $record->database_name,
            'username' => $record->database_username,
            'password' => $record->database_password,
        ]);

        Config::set("database.connections.{$connectionName}", $connectionConfig);

        return [
            'connection_name' => $connectionName,
            'config' => $connectionConfig,
            'record' => $record,
        ];
    }

    public function connect(PlatformTenant $tenant): string
    {
        $this->disconnect();

        $configured = $this->configureTenantConnection($tenant);
        $connectionName = $configured['connection_name'];

        DB::purge($connectionName);
        DB::reconnect($connectionName);
        DB::connection($connectionName)->getPdo();

        $this->originalDefaultConnection ??= Config::get('database.default');
        Config::set('database.default', $connectionName);

        $configured['record']->forceFill([
            'last_connected_at' => now(),
            'connection_status' => 'connected',
        ])->save();

        $this->tenantContext->setTenant($tenant);
        $this->activeConnectionName = $connectionName;

        return $connectionName;
    }

    public function disconnect(): void
    {
        if ($this->activeConnectionName) {
            DB::disconnect($this->activeConnectionName);
            DB::purge($this->activeConnectionName);
        }

        if ($this->originalDefaultConnection) {
            Config::set('database.default', $this->originalDefaultConnection);
        }

        $this->activeConnectionName = null;
        $this->tenantContext->clear();
    }

    public function testConnection(PlatformTenant $tenant): bool
    {
        $configured = $this->configureTenantConnection($tenant);
        $connectionName = $configured['connection_name'];
        $record = $configured['record'];

        try {
            DB::purge($connectionName);
            DB::reconnect($connectionName);
            DB::connection($connectionName)->select('select 1');

            $record->forceFill([
                'last_connected_at' => now(),
                'connection_status' => 'connected',
            ])->save();

            $this->audit->logTenantDatabaseConnectionTested($tenant, true, [
                'connection_name' => $connectionName,
                'database_name' => $record->database_name,
            ]);

            return true;
        } catch (Throwable $exception) {
            $record->forceFill([
                'connection_status' => 'failed',
            ])->save();

            $this->audit->logTenantDatabaseConnectionTested($tenant, false, [
                'connection_name' => $connectionName,
                'database_name' => $record->database_name,
                'message' => $exception->getMessage(),
            ]);

            return false;
        } finally {
            DB::disconnect($connectionName);
            DB::purge($connectionName);
        }
    }

    public function currentTenant(): ?PlatformTenant
    {
        return $this->tenantContext->getTenant();
    }

    public function currentTenantConnectionName(): ?string
    {
        return $this->activeConnectionName;
    }

    protected function activeDatabaseConnectionFor(PlatformTenant $tenant): TenantDatabaseConnection
    {
        $record = $tenant->relationLoaded('databaseConnection')
            ? $tenant->databaseConnection
            : $tenant->databaseConnection()->first();

        if (! $record || ! $record->is_active) {
            throw new RuntimeException('No active tenant database connection is configured.');
        }

        return $record;
    }

    protected function extractSubdomain(string $host): ?string
    {
        $host = trim(Str::before($host, ':'));

        if ($host === '' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $segments = array_values(array_filter(explode('.', $host)));

        if (count($segments) < 3) {
            return null;
        }

        return $segments[0] ?: null;
    }
}
