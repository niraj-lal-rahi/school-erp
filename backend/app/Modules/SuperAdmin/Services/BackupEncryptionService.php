<?php

namespace App\Modules\SuperAdmin\Services;

use App\Modules\SuperAdmin\Models\PlatformTenant;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class BackupEncryptionService
{
    public function __construct(
        protected TenantEncryptionKeyService $tenantKeys,
    ) {
    }

    /**
     * @return array{payload:string,checksum:string,key_version:int|null,algorithm:string}
     */
    public function encryptForTenant(PlatformTenant $tenant, string $plaintext): array
    {
        $tenantKey = $this->tenantKeys->getActiveKey($tenant);

        if (! $tenantKey) {
            throw new RuntimeException(sprintf(
                'No active tenant encryption key is available for tenant [%s].',
                $tenant->code
            ));
        }

        $keyMaterial = base64_decode((string) $tenantKey->encrypted_data_key, true);

        if ($keyMaterial === false || $keyMaterial === '') {
            throw new RuntimeException(sprintf(
                'Invalid tenant encryption key material for tenant [%s].',
                $tenant->code
            ));
        }

        $encrypted = (new Encrypter($keyMaterial, 'AES-256-CBC'))->encryptString($plaintext);

        return [
            'payload' => $encrypted,
            'checksum' => hash('sha256', $encrypted),
            'key_version' => (int) $tenantKey->key_version,
            'algorithm' => 'AES-256-CBC',
        ];
    }

    /**
     * @return array{payload:string,checksum:string,key_version:int|null,algorithm:string}
     */
    public function encryptForPlatform(string $plaintext): array
    {
        $encrypted = Crypt::encryptString($plaintext);

        return [
            'payload' => $encrypted,
            'checksum' => hash('sha256', $encrypted),
            'key_version' => null,
            'algorithm' => 'AES-256-CBC',
        ];
    }
}
