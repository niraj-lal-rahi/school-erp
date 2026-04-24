<?php

namespace App\Enums\HR;

enum StaffType: string
{
    case Teaching = 'teaching';
    case NonTeaching = 'non_teaching';
    case Admin = 'admin';
    case Support = 'support';
    case Driver = 'driver';
    case Other = 'other';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
