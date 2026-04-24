<?php

namespace App\Enums\Finance;

enum FineType: string
{
    case Fixed = 'fixed';
    case Daily = 'daily';
    case Percentage = 'percentage';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
