<?php

namespace App\Modules\Tenant\Casts;

class EncryptedArrayCast extends EncryptedJsonCast
{
    protected function restoreValue(string $value): mixed
    {
        $decoded = parent::restoreValue($value);

        return is_array($decoded) ? $decoded : [$decoded];
    }
}
