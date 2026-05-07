<?php

namespace App\Modules\Tenant\Services;

use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantDatabaseConnection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class TenantConnectionManager
{
    protected string $platformConnection = 'platform';

    protected string $tenantConnection = 'tenant';

    protected string $originalDefaultConnection;

    /**
     * @var array<string, mixed>
     */
    protected array $baseTenantConfig;

    public function __construct(
        protected DatabaseManager $database,
        protected TenantContext $tenantContext,
    ) {
        $this->originalDefaultConnection = (string) config('database.default');
        $this->baseTenantConfig = (array) config("database.connections.{$this->tenantConnection}", []);
    }

    public function resolveTenant(Request $request): ?PlatformTenant
    {
        $query = PlatformTenant::query()
            ->whereNull('deleted_at');

        if ($host = $this->normalizedHost($request)) {
            $tenant = (clone $query)->where('slug', $host)->first();

            if ($tenant instanceof PlatformTenant) {
                return $tenant;
            }
        }

        if ($host = $this->normalizedHost($request)) {
            $subdomain = $this->extractSubdomain($host);

            if ($subdomain !== null) {
                $tenant = (clone $query)
                    ->where(function ($builder) use ($subdomain): void {
                        $builder->where('slug', $subdomain)
                            ->orWhere('code', $subdomain);
                    })
                    ->first();

                if ($tenant instanceof PlatformTenant) {
                    return $tenant;
                }
            }
        }

        if ($tenantCode = $this->resolveTenantCodeFromHeader($request)) {
            return (clone $query)
                ->where(function ($builder) use ($tenantCode): void {
                    $builder->where('code', $tenantCode)
                        ->orWhere('slug', $tenantCode);
                })
                ->first();
        }

        if ($tenantCode = $this->resolveTenantCodeFromRoute($request)) {
            return (clone $query)
                ->where(function ($builder) use ($tenantCode): void {
                    $builder->where('code', $tenantCode)
                        ->orWhere('slug', $tenantCode);
                })
                ->first();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function configureTenantConnection(PlatformTenant $tenant): array
    {
        $connection = $this->activeConnectionForTenant($tenant);

        $config = array_replace($this->baseTenantConfig, [
            'driver' => $connection->database_driver ?: Arr::get($this->baseTenantConfig, 'driver', 'mysql'),
            'host' => $connection->database_host,
            'port' => $connection->database_port,
            'database' => $connection->database_name,
            'username' => $connection->database_username,
            'password' => $connection->database_password,
        ]);

        Config::set("database.connections.{$this->tenantConnection}", $config);

        return $config;
    }

    public function connect(PlatformTenant $tenant): void
    {
        $this->disconnect();

        $this->configureTenantConnection($tenant);

        $this->database->purge($this->tenantConnection);
        $this->database->setDefaultConnection($this->tenantConnection);
        $this->database->connection($this->tenantConnection)->getPdo();

        $tenant->activeDatabaseConnection()?->update([
            'last_connected_at' => now(),
            'connection_status' => 'connected',
        ]);

        $this->tenantContext->setTenant($tenant);
    }

    public function disconnect(): void
    {
        $this->database->disconnect($this->tenantConnection);
        $this->database->purge($this->tenantConnection);

        Config::set("database.connections.{$this->tenantConnection}", $this->baseTenantConfig);
        $this->database->setDefaultConnection($this->originalDefaultConnection);

        $this->tenantContext->clear();
    }

    public function testConnection(PlatformTenant $tenant): bool
    {
        $wasConnectedTenant = $this->tenantContext->currentTenant();
        $defaultConnectionBeforeTest = $this->database->getDefaultConnection();
        $tenantConfigBeforeTest = (array) config("database.connections.{$this->tenantConnection}", []);

        try {
            $this->configureTenantConnection($tenant);

            $this->database->purge($this->tenantConnection);
            $this->database->connection($this->tenantConnection)->getPdo();
            $this->database->connection($this->tenantConnection)->select('SELECT 1');

            $tenant->activeDatabaseConnection()?->update([
                'last_connected_at' => now(),
                'connection_status' => 'connected',
            ]);

            return true;
        } catch (\Throwable) {
            $tenant->activeDatabaseConnection()?->update([
                'connection_status' => 'failed',
            ]);

            return false;
        } finally {
            $this->database->disconnect($this->tenantConnection);
            $this->database->purge($this->tenantConnection);
            Config::set("database.connections.{$this->tenantConnection}", $tenantConfigBeforeTest ?: $this->baseTenantConfig);
            $this->database->setDefaultConnection($defaultConnectionBeforeTest ?: $this->originalDefaultConnection);

            if ($wasConnectedTenant instanceof PlatformTenant) {
                $this->tenantContext->setTenant($wasConnectedTenant);
            } else {
                $this->tenantContext->clear();
            }
        }
    }

    public function currentTenant(): ?PlatformTenant
    {
        return $this->tenantContext->currentTenant();
    }

    protected function activeConnectionForTenant(PlatformTenant $tenant): TenantDatabaseConnection
    {
        $tenant->loadMissing('activeDatabaseConnection');

        /** @var TenantDatabaseConnection|null $connection */
        $connection = $tenant->activeDatabaseConnection;

        if (! $connection || ! $connection->is_active) {
            throw new \RuntimeException('No active tenant database connection is configured.');
        }

        return $connection;
    }

    protected function resolveTenantCodeFromHeader(Request $request): ?string
    {
        $tenantCode = $request->header('X-Tenant-Code');

        if (is_string($tenantCode) && trim($tenantCode) !== '') {
            return trim($tenantCode);
        }

        return null;
    }

    protected function resolveTenantCodeFromRoute(Request $request): ?string
    {
        foreach (['tenant', 'tenantCode', 'tenantSlug'] as $parameter) {
            $value = $request->route($parameter);

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    protected function normalizedHost(Request $request): ?string
    {
        $host = $request->getHost();

        if (! is_string($host) || trim($host) === '') {
            return null;
        }

        $host = strtolower(trim($host));

        return str_contains($host, ':') ? (string) strtok($host, ':') : $host;
    }

    protected function extractSubdomain(string $host): ?string
    {
        if (filter_var($host, FILTER_VALIDATE_IP) || in_array($host, ['localhost', '127.0.0.1'], true)) {
            return null;
        }

        $segments = array_values(array_filter(explode('.', $host)));

        if (count($segments) < 3) {
            return null;
        }

        return $segments[0] ?: null;
    }
}
