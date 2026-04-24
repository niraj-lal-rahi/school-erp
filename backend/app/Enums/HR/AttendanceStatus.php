<?php

namespace App\Enums\HR;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Absent = 'absent';
    case HalfDay = 'half_day';
    case Late = 'late';
    case Leave = 'leave';
    case Holiday = 'holiday';

    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
