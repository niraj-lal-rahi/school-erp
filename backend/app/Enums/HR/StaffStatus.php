<?php

namespace App\Enums\HR;

enum StaffStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Resigned = 'resigned';
    case Terminated = 'terminated';
    case Retired = 'retired';
    case Suspended = 'suspended';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
