<?php

namespace App\Enums\HR;

enum PayrollRunStatus: string
{
    case Draft = 'draft';
    case Processing = 'processing';
    case Finalized = 'finalized';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
