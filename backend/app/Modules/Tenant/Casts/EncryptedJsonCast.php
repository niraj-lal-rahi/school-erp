<?php

namespace App\Modules\Tenant\Casts;

use App\Modules\Tenant\Casts\Support\AbstractTenantEncryptedCast;
use JsonException;
use RuntimeException;

class EncryptedJsonCast extends AbstractTenantEncryptedCast
{
    protected function serializeValue(mixed $value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to JSON-encode encrypted tenant value.', previous: $exception);
        }
    }

    protected function restoreValue(string $value): mixed
    {
        try {
            return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Unable to JSON-decode encrypted tenant value.', previous: $exception);
        }
    }
}
