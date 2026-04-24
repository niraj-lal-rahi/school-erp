<?php

namespace App\Enums\Finance;

enum FeeAmountType: string
{
    case Fixed = 'fixed';
    case Variable = 'variable';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
