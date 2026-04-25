<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendanceHoliday;
use App\Models\User;

class AttendanceHolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceHoliday $holiday): bool
    {
        return $user->school_id === $holiday->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, AttendanceHoliday $holiday): bool
    {
        return $user->school_id === $holiday->school_id && $user->hasPermission('attendance.manage');
    }

    public function delete(User $user, AttendanceHoliday $holiday): bool
    {
        return $user->school_id === $holiday->school_id && $user->hasPermission('attendance.manage');
    }
}
