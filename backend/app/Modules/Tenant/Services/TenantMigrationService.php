<?php

namespace App\Modules\Tenant\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use Illuminate\Support\Facades\Artisan;

class TenantMigrationService
{
    /**
     * @var list<string>
     */
    protected array $tenantSeeders = [
        'Database\\Seeders\\Auth\\PermissionSeeder',
        'Database\\Seeders\\Settings\\SettingGroupSeeder',
        'Database\\Seeders\\Settings\\FeatureFlagSeeder',
        'Database\\Seeders\\Settings\\DefaultBrandingSeeder',
        'Database\\Seeders\\Settings\\DefaultLocalizationSeeder',
        'Database\\Seeders\\Settings\\DefaultSecuritySettingSeeder',
        'Database\\Seeders\\Settings\\DefaultSettingSeeder',
    ];

    public function __construct(
        protected TenantConnectionManager $tenantConnections,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function runMigrations(PlatformTenant $tenant): array
    {
        return $this->executeForTenant($tenant, function () use ($tenant): array {
            $exitCode = Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $output = trim(Artisan::output());
            $successful = $exitCode === 0;

            $this->logAction(
                $tenant,
                $successful ? 'tenant_migrations_completed' : 'tenant_migrations_failed',
                'tenant_database',
                $successful ? 'Tenant migrations completed.' : 'Tenant migrations failed.',
                [
                    'exit_code' => $exitCode,
                    'output' => $output,
                ],
            );

            return [
                'success' => $successful,
                'exit_code' => $exitCode,
                'output' => $output,
                'errors' => $successful ? [] : [$output ?: 'Tenant migration command failed.'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function runSeeders(PlatformTenant $tenant): array
    {
        return $this->executeForTenant($tenant, function () use ($tenant): array {
            $results = [];
            $errors = [];

            foreach ($this->tenantSeeders as $seederClass) {
                $exitCode = Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--class' => $seederClass,
                    '--force' => true,
                ]);

                $output = trim(Artisan::output());
                $successful = $exitCode === 0;

                $results[] = [
                    'seeder' => $seederClass,
                    'success' => $successful,
                    'exit_code' => $exitCode,
                    'output' => $output,
                ];

                if (! $successful) {
                    $errors[] = [
                        'seeder' => $seederClass,
                        'message' => $output ?: sprintf('Seeder failed: %s', $seederClass),
                    ];
                }
            }

            $successful = $errors === [];

            $this->logAction(
                $tenant,
                $successful ? 'tenant_seeders_completed' : 'tenant_seeders_failed',
                'tenant_database',
                $successful ? 'Tenant seeders completed.' : 'One or more tenant seeders failed.',
                [
                    'results' => $results,
                ],
            );

            return [
                'success' => $successful,
                'results' => $results,
                'errors' => $errors,
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function getMigrationStatus(PlatformTenant $tenant): array
    {
        return $this->executeForTenant($tenant, function () use ($tenant): array {
            $exitCode = Artisan::call('migrate:status', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
            ]);

            $output = trim(Artisan::output());
            $successful = $exitCode === 0;

            $parsed = $this->parseMigrationStatusOutput($output);

            $this->logAction(
                $tenant,
                $successful ? 'tenant_migration_status_checked' : 'tenant_migration_status_failed',
                'tenant_database',
                $successful ? 'Tenant migration status checked.' : 'Tenant migration status check failed.',
                [
                    'exit_code' => $exitCode,
                    'output' => $output,
                    'parsed' => $parsed,
                ],
            );

            return [
                'success' => $successful,
                'exit_code' => $exitCode,
                'output' => $output,
                'status' => $parsed,
                'errors' => $successful ? [] : [$output ?: 'Unable to read tenant migration status.'],
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function retryFailedMigration(PlatformTenant $tenant): array
    {
        return $this->executeForTenant($tenant, function () use ($tenant): array {
            $migrationResult = $this->runMigrations($tenant);

            if (! $migrationResult['success']) {
                $this->logAction(
                    $tenant,
                    'tenant_migration_retry_failed',
                    'tenant_database',
                    'Tenant migration retry failed during migration phase.',
                    [
                        'migration_result' => $migrationResult,
                    ],
                );

                return [
                    'success' => false,
                    'migration' => $migrationResult,
                    'seeders' => null,
                    'errors' => $migrationResult['errors'] ?? ['Tenant migration retry failed.'],
                ];
            }

            $seedResult = $this->runSeeders($tenant);
            $successful = $seedResult['success'] ?? false;

            $this->logAction(
                $tenant,
                $successful ? 'tenant_migration_retry_completed' : 'tenant_migration_retry_partially_failed',
                'tenant_database',
                $successful ? 'Tenant migration retry completed.' : 'Tenant migration retry completed, but seeders failed.',
                [
                    'migration_result' => $migrationResult,
                    'seed_result' => $seedResult,
                ],
            );

            return [
                'success' => $successful,
                'migration' => $migrationResult,
                'seeders' => $seedResult,
                'errors' => $successful ? [] : ($seedResult['errors'] ?? ['Tenant seeding failed after migration retry.']),
            ];
        });
    }

    /**
     * @template TReturn of array<string, mixed>
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function executeForTenant(PlatformTenant $tenant, callable $callback): array
    {
        try {
            $this->tenantConnections->connect($tenant);

            return $callback();
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function parseMigrationStatusOutput(string $output): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $output) ?: [];
        $statuses = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || ! str_contains($line, '|')) {
                continue;
            }

            if (str_contains($line, 'Migration name') || preg_match('/^\+\-+/', $line)) {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', explode('|', trim($line, '|'))), static fn ($value) => $value !== ''));

            if (count($parts) < 2) {
                continue;
            }

            $statuses[] = [
                'ran' => $parts[0],
                'migration' => $parts[1],
                'batch' => $parts[2] ?? '',
            ];
        }

        return $statuses;
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
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => null,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => null,
            'user_agent' => null,
            'metadata' => $metadata,
        ]);
    }
}
