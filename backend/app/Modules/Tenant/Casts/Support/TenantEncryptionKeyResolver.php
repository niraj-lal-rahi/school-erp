<?php

namespace App\Modules\Tenant\Casts\Support;

use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\TenantEncryptionKey;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantEncryptionKeyResolver
{
    protected string $tenantConnection = 'tenant';

    public function resolveEncrypter(Model $model, ?int $keyVersion = null): Encrypter
    {
        $tenantId = $this->resolvePlatformTenantId($model);

        $tenantKey = TenantEncryptionKey::query()
            ->where('tenant_id', $tenantId)
            ->when(
                $keyVersion !== null,
                fn ($query) => $query->where('key_version', $keyVersion),
                fn ($query) => $query->where('status', 'active')->orderByDesc('key_version')
            )
            ->first();

        if (! $tenantKey) {
            throw new RuntimeException(sprintf(
                'Missing tenant encryption key for model [%s].',
                $model::class
            ));
        }

        $decodedKey = base64_decode((string) $tenantKey->encrypted_data_key, true);

        if ($decodedKey === false || $decodedKey === '') {
            throw new RuntimeException(sprintf(
                'Invalid tenant encryption key material for tenant [%d], version [%d].',
                $tenantId,
                (int) $tenantKey->key_version
            ));
        }

        return new Encrypter($decodedKey, 'AES-256-CBC');
    }

    public function resolveCurrentKeyVersion(Model $model): int
    {
        $tenantId = $this->resolvePlatformTenantId($model);

        $tenantKey = TenantEncryptionKey::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderByDesc('key_version')
            ->first();

        if (! $tenantKey) {
            throw new RuntimeException(sprintf(
                'Missing active tenant encryption key for model [%s].',
                $model::class
            ));
        }

        return (int) $tenantKey->key_version;
    }

    public function shouldBypass(Model $model): bool
    {
        return array_key_exists('is_encrypted', $model->getAttributes())
            && ! (bool) $model->getAttribute('is_encrypted');
    }

    public function shouldHideForCurrentContext(): bool
    {
        if (! app()->bound('request')) {
            return false;
        }

        /** @var Request $request */
        $request = app('request');
        $impersonationContext = $request->attributes->get('impersonationContext');

        if (! is_array($impersonationContext)) {
            return false;
        }

        $emergencyAccessContext = $request->attributes->get('emergencyAccessContext');

        if (is_array($emergencyAccessContext) && ! empty($emergencyAccessContext['id'])) {
            return false;
        }

        return true;
    }

    protected function resolvePlatformTenantId(Model $model): int
    {
        $schoolId = $model->getAttribute('school_id');

        if (! $schoolId) {
            throw new RuntimeException(sprintf(
                'Unable to resolve tenant encryption context for model [%s] without school_id.',
                $model::class
            ));
        }

        $school = DB::connection($this->tenantConnection)
            ->table('schools')
            ->select(['code', 'slug'])
            ->where('id', $schoolId)
            ->first();

        if (! $school) {
            throw new RuntimeException(sprintf(
                'Unable to resolve tenant school record [%d] for model [%s].',
                $schoolId,
                $model::class
            ));
        }

        $platformTenant = PlatformTenant::query()
            ->where('code', $school->code)
            ->orWhere('slug', $school->slug)
            ->first();

        if (! $platformTenant) {
            throw new RuntimeException(sprintf(
                'Unable to resolve platform tenant for school [%d] on model [%s].',
                $schoolId,
                $model::class
            ));
        }

        return (int) $platformTenant->id;
    }
}
