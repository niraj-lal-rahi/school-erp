<?php

namespace App\Enums\Finance;

enum FeeAssignmentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
