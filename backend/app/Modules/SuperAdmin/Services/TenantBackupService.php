<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Jobs\TenantBackupJob;
use App\Modules\SuperAdmin\Models\EmergencyAccessLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantBackupLog;
use App\Modules\Tenant\Services\TenantConnectionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TenantBackupService
{
    protected string $platformConnection = 'platform';
    protected string $tenantConnection = 'tenant';
    protected string $defaultDisk = 'private';

    /**
     * @var list<string>
     */
    protected array $allowedBackupTypes = [
        'tenant_database',
        'tenant_documents',
        'platform_database',
    ];

    public function __construct(
        protected TenantConnectionManager $tenantConnections,
        protected BackupEncryptionService $encryption,
        protected PlatformAuditService $audit,
        protected EmergencyAccessService $emergencyAccess,
    ) {
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, TenantBackupLog>
     */
    public function listBackups(PlatformTenant $tenant, array $filters = []): Collection
    {
        return TenantBackupLog::query()
            ->where('tenant_id', $tenant->id)
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->when(! empty($filters['backup_type']), fn ($query) => $query->where('backup_type', $filters['backup_type']))
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @param  list<string>  $requestedTypes
     * @return Collection<int, TenantBackupLog>
     */
    public function triggerManualBackup(
        PlatformTenant $tenant,
        ?int $requestedByUserId = null,
        array $requestedTypes = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): Collection {
        $backupTypes = $requestedTypes === []
            ? ['tenant_database', 'tenant_documents']
            : array_values(array_intersect($requestedTypes, $this->allowedBackupTypes));

        if ($backupTypes === []) {
            throw new RuntimeException('No valid backup types were requested.');
        }

        $logs = collect();

        foreach ($backupTypes as $backupType) {
            $log = TenantBackupLog::query()->create([
                'tenant_id' => $tenant->id,
                'backup_type' => $backupType,
                'storage_disk' => $this->defaultDisk,
                'file_path' => null,
                'file_size_bytes' => null,
                'checksum' => null,
                'is_encrypted' => true,
                'status' => 'queued',
                'started_at' => null,
                'completed_at' => null,
                'error_message' => null,
                'metadata' => [
                    'requested_by_user_id' => $requestedByUserId,
                    'requested_at' => now()->toIso8601String(),
                    'restore_requires_emergency_access' => true,
                ],
            ]);

            TenantBackupJob::dispatch($log->id);
            $logs->push($log);
        }

        $this->audit->record(
            action: 'tenant_backup_requested',
            module: 'tenant_backup',
            description: 'Manual tenant backup requested.',
            tenantId: $tenant->id,
            userId: $requestedByUserId,
            metadata: [
                'backup_types' => $backupTypes,
                'backup_log_ids' => $logs->pluck('id')->all(),
            ],
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return $logs;
    }

    public function processBackupLog(int $backupLogId): TenantBackupLog
    {
        /** @var TenantBackupLog $log */
        $log = TenantBackupLog::query()->findOrFail($backupLogId);
        /** @var PlatformTenant $tenant */
        $tenant = PlatformTenant::query()->findOrFail($log->tenant_id);

        $log->update([
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $backupPayload = match ($log->backup_type) {
                'tenant_database' => $this->buildTenantDatabaseBackup($tenant),
                'tenant_documents' => $this->buildTenantDocumentsBackup($tenant),
                'platform_database' => $this->buildPlatformDatabaseBackup($tenant),
                default => throw new RuntimeException(sprintf('Unsupported backup type [%s].', $log->backup_type)),
            };

            $encryption = $log->backup_type === 'platform_database'
                ? $this->encryption->encryptForPlatform($backupPayload)
                : $this->encryption->encryptForTenant($tenant, $backupPayload);

            $filePath = $this->storeEncryptedBackup($tenant, $log, $encryption['payload']);
            $fileSize = Storage::disk($this->defaultDisk)->size($filePath);

            $log->update([
                'storage_disk' => $this->defaultDisk,
                'file_path' => $filePath,
                'file_size_bytes' => $fileSize,
                'checksum' => $encryption['checksum'],
                'is_encrypted' => true,
                'status' => 'completed',
                'completed_at' => now(),
                'metadata' => array_merge($log->metadata ?? [], [
                    'encryption_algorithm' => $encryption['algorithm'],
                    'encryption_key_version' => $encryption['key_version'],
                    'snapshot_summary' => $this->extractSnapshotSummary($log->backup_type, $backupPayload),
                ]),
            ]);

            $this->audit->record(
                action: $log->backup_type === 'platform_database' ? 'platform_backup_completed' : 'tenant_backup_completed',
                module: 'tenant_backup',
                description: 'Backup completed successfully.',
                tenantId: $tenant->id,
                userId: data_get($log->metadata, 'requested_by_user_id'),
                metadata: [
                    'backup_log_id' => $log->id,
                    'backup_type' => $log->backup_type,
                    'file_path' => $filePath,
                    'checksum' => $encryption['checksum'],
                ],
            );

            return $log->fresh() ?? $log;
        } catch (Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            $this->audit->record(
                action: 'tenant_backup_failed',
                module: 'tenant_backup',
                description: 'Backup failed.',
                tenantId: $tenant->id,
                userId: data_get($log->metadata, 'requested_by_user_id'),
                metadata: [
                    'backup_log_id' => $log->id,
                    'backup_type' => $log->backup_type,
                    'error' => $exception->getMessage(),
                ],
            );

            throw $exception;
        } finally {
            $this->tenantConnections->disconnect();
        }
    }

    public function prepareRestoreFoundation(
        TenantBackupLog $backup,
        EmergencyAccessLog $approvedAccess,
        int $platformAdminUserId,
    ): array {
        $validatedAccess = $this->emergencyAccess->validateApprovedAccess(
            logId: $approvedAccess->id,
            tenantId: $backup->tenant_id,
            platformAdminUserId: $platformAdminUserId,
        );

        if (! $validatedAccess) {
            throw new RuntimeException('Restore requires approved emergency access.');
        }

        $this->audit->backupRestored(
            tenantId: $backup->tenant_id,
            userId: $platformAdminUserId,
            metadata: [
                'backup_log_id' => $backup->id,
                'mode' => 'restore_foundation_only',
            ],
        );

        return [
            'backup_log_id' => $backup->id,
            'status' => 'restore_precheck_passed',
            'restore_requires_manual_implementation' => true,
        ];
    }

    protected function buildTenantDatabaseBackup(PlatformTenant $tenant): string
    {
        return $this->executeOnTenant($tenant, function () use ($tenant): string {
            $connection = DB::connection($this->tenantConnection);
            $tableNames = collect($connection->select('SHOW TABLES'))
                ->map(function (object $row): string {
                    return (string) array_values((array) $row)[0];
                })
                ->values();

            $tables = $tableNames->map(function (string $table) use ($connection): array {
                return [
                    'table' => $table,
                    'row_count' => (int) ($connection->table($table)->count()),
                ];
            })->all();

            return json_encode([
                'kind' => 'tenant_database_foundation',
                'tenant_id' => $tenant->id,
                'tenant_code' => $tenant->code,
                'captured_at' => now()->toIso8601String(),
                'tables' => $tables,
            ], JSON_THROW_ON_ERROR);
        });
    }

    protected function buildTenantDocumentsBackup(PlatformTenant $tenant): string
    {
        return $this->executeOnTenant($tenant, function () use ($tenant): string {
            $connection = DB::connection($this->tenantConnection);
            $documents = [];

            if ($this->tableExists('document_files')) {
                $documents = $connection->table('document_files')
                    ->select(['id', 'document_id', 'disk', 'path', 'file_name', 'file_size'])
                    ->orderBy('id')
                    ->limit(5000)
                    ->get()
                    ->map(fn (object $row): array => (array) $row)
                    ->all();
            }

            return json_encode([
                'kind' => 'tenant_documents_foundation',
                'tenant_id' => $tenant->id,
                'tenant_code' => $tenant->code,
                'captured_at' => now()->toIso8601String(),
                'documents' => $documents,
                'document_count' => count($documents),
            ], JSON_THROW_ON_ERROR);
        });
    }

    protected function buildPlatformDatabaseBackup(PlatformTenant $tenant): string
    {
        $connection = DB::connection($this->platformConnection);
        $tables = [
            'platform_tenants',
            'tenant_database_connections',
            'tenant_subscriptions',
            'tenant_feature_access',
            'tenant_security_settings',
            'platform_settings',
        ];

        $summary = collect($tables)
            ->filter(fn (string $table): bool => $this->platformTableExists($table))
            ->map(function (string $table) use ($connection, $tenant): array {
                $query = $connection->table($table);

                if ($connection->getSchemaBuilder()->hasColumn($table, 'tenant_id')) {
                    $query->where('tenant_id', $tenant->id);
                } elseif ($table === 'platform_tenants') {
                    $query->where('id', $tenant->id);
                }

                return [
                    'table' => $table,
                    'row_count' => (int) $query->count(),
                ];
            })
            ->values()
            ->all();

        return json_encode([
            'kind' => 'platform_database_foundation',
            'tenant_id' => $tenant->id,
            'tenant_code' => $tenant->code,
            'captured_at' => now()->toIso8601String(),
            'tables' => $summary,
        ], JSON_THROW_ON_ERROR);
    }

    protected function storeEncryptedBackup(PlatformTenant $tenant, TenantBackupLog $log, string $encryptedPayload): string
    {
        $directory = sprintf('backups/tenants/%d/%s', $tenant->id, $log->backup_type);
        $filename = sprintf('%s-%d-%s.enc', now()->format('YmdHis'), $log->id, $log->backup_type);
        $path = $directory.'/'.$filename;

        Storage::disk($this->defaultDisk)->put($path, $encryptedPayload);

        return $path;
    }

    protected function tableExists(string $table): bool
    {
        return DB::connection($this->tenantConnection)->getSchemaBuilder()->hasTable($table);
    }

    protected function platformTableExists(string $table): bool
    {
        return DB::connection($this->platformConnection)->getSchemaBuilder()->hasTable($table);
    }

    /**
     * @return array<string, mixed>
     */
    protected function extractSnapshotSummary(string $backupType, string $payload): array
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return [
                'backup_type' => $backupType,
            ];
        }

        return match ($backupType) {
            'tenant_database', 'platform_database' => [
                'table_count' => count($decoded['tables'] ?? []),
            ],
            'tenant_documents' => [
                'document_count' => (int) ($decoded['document_count'] ?? 0),
            ],
            default => [
                'backup_type' => $backupType,
            ],
        };
    }

    /**
     * @template TReturn
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function executeOnTenant(PlatformTenant $tenant, callable $callback): mixed
    {
        try {
            $this->tenantConnections->connect($tenant);

            return $callback();
        } finally {
            $this->tenantConnections->disconnect();
        }
    }
}
