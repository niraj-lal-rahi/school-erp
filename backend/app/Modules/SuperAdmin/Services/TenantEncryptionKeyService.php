<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantEncryptionKey;
use App\Modules\SuperAdmin\Models\TenantKeyRotationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantEncryptionKeyService
{
    protected string $platformConnection = 'platform';

    public function createInitialKey(
        PlatformTenant $tenant,
        ?int $createdByUserId = null,
        ?string $remarks = null,
    ): TenantEncryptionKey {
        $existing = $this->getActiveKey($tenant);

        if ($existing) {
            return $existing;
        }

        return DB::connection($this->platformConnection)->transaction(function () use ($tenant, $createdByUserId, $remarks): TenantEncryptionKey {
            $key = TenantEncryptionKey::query()->create([
                'tenant_id' => $tenant->id,
                'key_reference' => $this->generateKeyReference($tenant, 1),
                'encrypted_data_key' => $this->generateDataKey(),
                'key_version' => 1,
                'status' => 'active',
                'activated_at' => now(),
                'rotated_at' => null,
            ]);

            TenantKeyRotationLog::query()->create([
                'tenant_id' => $tenant->id,
                'old_key_version' => 0,
                'new_key_version' => 1,
                'rotated_by' => $createdByUserId,
                'status' => 'completed',
                'remarks' => $remarks ?: 'Initial tenant encryption key created.',
            ]);

            $this->logAudit(
                $tenant,
                'tenant_encryption_key_created',
                'tenant_security',
                'Initial tenant encryption key created.',
                [
                    'key_reference' => $key->key_reference,
                    'key_version' => $key->key_version,
                ],
                $createdByUserId,
            );

            return $key;
        });
    }

    public function getActiveKey(PlatformTenant $tenant): ?TenantEncryptionKey
    {
        return TenantEncryptionKey::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->orderByDesc('key_version')
            ->first();
    }

    public function rotateKey(
        PlatformTenant $tenant,
        ?int $rotatedByUserId = null,
        ?string $remarks = null,
    ): TenantEncryptionKey {
        return DB::connection($this->platformConnection)->transaction(function () use ($tenant, $rotatedByUserId, $remarks): TenantEncryptionKey {
            $activeKey = $this->getActiveKey($tenant);
            $oldVersion = $activeKey?->key_version ?? 0;
            $newVersion = $oldVersion + 1;

            if ($activeKey) {
                $activeKey->update([
                    'status' => 'retired',
                    'rotated_at' => now(),
                ]);
            }

            $newKey = TenantEncryptionKey::query()->create([
                'tenant_id' => $tenant->id,
                'key_reference' => $this->generateKeyReference($tenant, $newVersion),
                'encrypted_data_key' => $this->generateDataKey(),
                'key_version' => $newVersion,
                'status' => 'active',
                'activated_at' => now(),
                'rotated_at' => null,
            ]);

            TenantKeyRotationLog::query()->create([
                'tenant_id' => $tenant->id,
                'old_key_version' => $oldVersion,
                'new_key_version' => $newVersion,
                'rotated_by' => $rotatedByUserId,
                'status' => 'completed',
                'remarks' => $remarks ?: 'Tenant encryption key rotated successfully.',
            ]);

            $this->logAudit(
                $tenant,
                'tenant_encryption_key_rotated',
                'tenant_security',
                'Tenant encryption key rotated.',
                [
                    'old_key_version' => $oldVersion,
                    'new_key_version' => $newVersion,
                    'new_key_reference' => $newKey->key_reference,
                ],
                $rotatedByUserId,
            );

            return $newKey;
        });
    }

    protected function generateDataKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    protected function generateKeyReference(PlatformTenant $tenant, int $version): string
    {
        return sprintf(
            'tenant/%s/key/v%d/%s',
            $tenant->code ?: $tenant->id,
            $version,
            Str::lower(Str::random(12))
        );
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAudit(
        PlatformTenant $tenant,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $performedByUserId = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenant->id,
            'user_id' => $performedByUserId,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => null,
            'user_agent' => null,
            'metadata' => $metadata,
        ]);
    }
}
