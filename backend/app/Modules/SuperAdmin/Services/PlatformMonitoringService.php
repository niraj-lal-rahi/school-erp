<?php

namespace App\Modules\SuperAdmin\Services;

use App\Models\Payments\PaymentWebhookEvent;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\SystemHealthLog;
use App\Modules\Tenant\Services\TenantConnectionManager;
use App\Services\Queue\FailedJobMonitoringService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PlatformMonitoringService
{
    public function __construct(
        protected FailedJobMonitoringService $failedJobs,
        protected TenantConnectionManager $tenantConnections,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function platformHealth(): array
    {
        $checkedAt = now()->toISOString();
        $components = [
            $this->databaseHealth(),
            $this->queueHealth(),
            $this->cacheHealth(),
            $this->storageHealth(),
            $this->cronHealth(),
        ];

        $status = collect($components)->contains(fn (array $component) => $component['status'] === 'failed')
            ? 'degraded'
            : 'healthy';

        return [
            'status' => $status,
            'checked_at' => $checkedAt,
            'components' => $components,
            'summary' => [
                'failed_jobs' => $this->failedJobs->summary(24),
                'webhook_failures_last_24h' => $this->paymentWebhookFailures(24),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function tenantHealth(PlatformTenant $tenant): array
    {
        $dbHealthy = $this->tenantConnections->testConnection($tenant);
        $storageUsage = $this->measureTenantStorageUsage($tenant);
        $webhookFailures = $this->measureTenantWebhookFailures($tenant);

        $components = [
            [
                'component' => 'tenant_database',
                'status' => $dbHealthy ? 'healthy' : 'failed',
                'message' => $dbHealthy ? 'Tenant database reachable.' : 'Tenant database connection failed.',
            ],
            [
                'component' => 'storage_usage',
                'status' => 'healthy',
                'message' => 'Tenant storage usage calculated.',
                'metadata' => $storageUsage,
            ],
            [
                'component' => 'payment_webhooks',
                'status' => ($webhookFailures['failed_count'] ?? 0) > 0 ? 'degraded' : 'healthy',
                'message' => 'Tenant payment webhook failure summary calculated.',
                'metadata' => $webhookFailures,
            ],
        ];

        $status = collect($components)->contains(fn (array $component) => $component['status'] === 'failed')
            ? 'degraded'
            : 'healthy';

        $health = [
            'status' => $status,
            'checked_at' => now()->toISOString(),
            'components' => $components,
            'summary' => [
                'tenant_id' => $tenant->id,
                'tenant_code' => $tenant->code,
                'database_connection_status' => $tenant->activeDatabaseConnection?->connection_status,
            ],
        ];

        SystemHealthLog::query()->create([
            'tenant_id' => $tenant->id,
            'component' => 'tenant_database',
            'check_name' => 'tenant_health',
            'status' => $status,
            'response_time_ms' => null,
            'message' => 'Tenant health snapshot captured.',
            'metadata' => $health['summary'],
            'checked_at' => now(),
        ]);

        return $health;
    }

    protected function databaseHealth(): array
    {
        $startedAt = microtime(true);

        try {
            DB::connection('platform')->select('SELECT 1');

            return [
                'component' => 'platform_database',
                'status' => 'healthy',
                'response_time_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'message' => 'Platform database reachable.',
            ];
        } catch (\Throwable $exception) {
            return [
                'component' => 'platform_database',
                'status' => 'failed',
                'response_time_ms' => (int) ((microtime(true) - $startedAt) * 1000),
                'message' => $exception->getMessage(),
            ];
        }
    }

    protected function queueHealth(): array
    {
        $summary = $this->failedJobs->summary(24);
        $failedCount = (int) ($summary['total_failures'] ?? 0);

        return [
            'component' => 'queue',
            'status' => $failedCount > 0 ? 'degraded' : 'healthy',
            'message' => $failedCount > 0
                ? sprintf('%d failed jobs in the last 24 hours.', $failedCount)
                : 'No failed jobs in the last 24 hours.',
            'metadata' => $summary,
        ];
    }

    protected function cacheHealth(): array
    {
        $key = 'platform:monitoring:health-check';

        try {
            Cache::put($key, 'ok', now()->addMinutes(1));
            $value = Cache::get($key);

            return [
                'component' => 'cache',
                'status' => $value === 'ok' ? 'healthy' : 'failed',
                'message' => $value === 'ok' ? 'Cache read/write successful.' : 'Cache read/write failed.',
            ];
        } catch (\Throwable $exception) {
            return [
                'component' => 'cache',
                'status' => 'failed',
                'message' => $exception->getMessage(),
            ];
        } finally {
            Cache::forget($key);
        }
    }

    protected function storageHealth(): array
    {
        $logDirectory = storage_path('logs');

        return [
            'component' => 'storage',
            'status' => File::exists($logDirectory) ? 'healthy' : 'failed',
            'message' => File::exists($logDirectory) ? 'Storage path reachable.' : 'Storage log path missing.',
            'metadata' => [
                'path' => $logDirectory,
            ],
        ];
    }

    protected function cronHealth(): array
    {
        $latest = SystemHealthLog::query()
            ->where('component', 'scheduler')
            ->latest('checked_at')
            ->first();

        if (! $latest) {
            return [
                'component' => 'cron',
                'status' => 'unknown',
                'message' => 'No scheduler heartbeat recorded yet.',
            ];
        }

        $stale = $latest->checked_at?->lt(now()->subMinutes(10)) ?? true;

        return [
            'component' => 'cron',
            'status' => $stale ? 'degraded' : 'healthy',
            'message' => $stale ? 'Scheduler heartbeat is stale.' : 'Scheduler heartbeat is recent.',
            'metadata' => [
                'checked_at' => $latest->checked_at?->toISOString(),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function measureTenantStorageUsage(PlatformTenant $tenant): array
    {
        return $this->executeTenantMetric($tenant, function (): array {
            if (! DB::connection('tenant')->getSchemaBuilder()->hasTable('document_files')) {
                return ['document_files_bytes' => 0];
            }

            $totalBytes = (int) DB::connection('tenant')->table('document_files')->sum('file_size');

            return [
                'document_files_bytes' => $totalBytes,
                'document_files_mb' => round($totalBytes / 1024 / 1024, 2),
            ];
        }, ['document_files_bytes' => 0, 'document_files_mb' => 0]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function measureTenantWebhookFailures(PlatformTenant $tenant): array
    {
        return $this->executeTenantMetric($tenant, function (): array {
            if (! DB::connection('tenant')->getSchemaBuilder()->hasTable('payment_webhook_events')) {
                return ['failed_count' => 0];
            }

            $failedCount = PaymentWebhookEvent::query()
                ->where(function ($query): void {
                    $query->where('processed', false)
                        ->whereNotNull('error_message');
                })
                ->count();

            return [
                'failed_count' => $failedCount,
            ];
        }, ['failed_count' => 0]);
    }

    protected function paymentWebhookFailures(int $hours): array
    {
        $count = 0;

        try {
            $count = PaymentWebhookEvent::query()
                ->withoutGlobalScopes()
                ->where('created_at', '>=', now()->subHours($hours))
                ->where(function ($query): void {
                    $query->where('processed', false)
                        ->whereNotNull('error_message');
                })
                ->count();
        } catch (\Throwable) {
            $count = 0;
        }

        return [
            'window_hours' => $hours,
            'failed_count' => $count,
        ];
    }

    /**
     * @template TReturn of array<string, mixed>
     *
     * @param  callable(): TReturn  $callback
     * @param  TReturn  $fallback
     * @return TReturn
     */
    protected function executeTenantMetric(PlatformTenant $tenant, callable $callback, array $fallback): array
    {
        try {
            $this->tenantConnections->connect($tenant);

            return $callback();
        } catch (\Throwable) {
            return $fallback;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }
}
