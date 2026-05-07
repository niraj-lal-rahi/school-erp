<?php

namespace App\Modules\Tenant\Casts\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;
use RuntimeException;

abstract class AbstractTenantEncryptedCast implements CastsAttributes
{
    protected TenantEncryptionKeyResolver $resolver;

    public function __construct()
    {
        $this->resolver = new TenantEncryptionKeyResolver();
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($this->resolver->shouldBypass($model)) {
            return $this->restorePlainValue($value);
        }

        if ($this->resolver->shouldHideForCurrentContext()) {
            return null;
        }

        [$keyVersion, $payload] = $this->extractPayload((string) $value);
        $encrypter = $this->resolver->resolveEncrypter($model, $keyVersion);

        try {
            return $this->restoreValue($encrypter->decryptString($payload));
        } catch (DecryptException $exception) {
            throw new RuntimeException(sprintf(
                'Unable to decrypt attribute [%s] on model [%s].',
                $key,
                $model::class
            ), previous: $exception);
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($this->resolver->shouldBypass($model)) {
            return $this->serializePlainValue($value);
        }

        $keyVersion = $this->resolver->resolveCurrentKeyVersion($model);
        $encrypter = $this->resolver->resolveEncrypter($model, $keyVersion);
        $encryptedPayload = $encrypter->encryptString($this->serializeValue($value));

        try {
            return json_encode([
                'v' => $keyVersion,
                'p' => $encryptedPayload,
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(sprintf(
                'Unable to encode encrypted payload for attribute [%s] on model [%s].',
                $key,
                $model::class
            ), previous: $exception);
        }
    }

    /**
     * @return array{0:int|null,1:string}
     */
    protected function extractPayload(string $value): array
    {
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            if (is_array($decoded) && isset($decoded['p'])) {
                return [
                    isset($decoded['v']) ? (int) $decoded['v'] : null,
                    (string) $decoded['p'],
                ];
            }
        } catch (JsonException) {
            // Fall back to supporting legacy raw encrypted payloads.
        }

        return [null, $value];
    }

    protected function restorePlainValue(mixed $value): mixed
    {
        return $value;
    }

    protected function serializePlainValue(mixed $value): mixed
    {
        return $value;
    }

    abstract protected function serializeValue(mixed $value): string;

    abstract protected function restoreValue(string $value): mixed;
}
