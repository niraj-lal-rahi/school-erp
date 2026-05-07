<?php

namespace App\Modules\Tenant\Casts;

class EncryptedPhoneCast extends EncryptedStringCast
{
    protected function serializeValue(mixed $value): string
    {
        $phone = preg_replace('/[^\d+]/', '', trim((string) $value)) ?? '';

        return $phone;
    }
}
