<?php

namespace App\Enums\HR;

enum SalaryComponentType: string
{
    case Earning = 'earning';
    case Deduction = 'deduction';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
