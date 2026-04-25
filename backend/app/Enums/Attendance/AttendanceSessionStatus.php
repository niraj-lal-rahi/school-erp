<?php

namespace App\Enums\Attendance;

enum AttendanceSessionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Locked = 'locked';
}
