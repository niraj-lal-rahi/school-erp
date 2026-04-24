<?php

namespace App\Enums\HR;

enum AttendanceSource: string
{
    case Manual = 'manual';
    case Biometric = 'biometric';
    case Import = 'import';
    case Mobile = 'mobile';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
