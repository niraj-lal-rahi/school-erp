<?php

namespace App\Enums\Attendance;

enum AttendanceSessionType: string
{
    case Daily = 'daily';
    case Period = 'period';
}
