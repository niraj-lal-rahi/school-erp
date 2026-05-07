<?php

namespace App\Modules\Tenant\Casts;

use App\Modules\Tenant\Casts\Support\AbstractTenantEncryptedCast;

class EncryptedStringCast extends AbstractTenantEncryptedCast
{
    protected function serializeValue(mixed $value): string
    {
        return trim((string) $value);
    }

    protected function restoreValue(string $value): mixed
    {
        return $value;
    }
}
