<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendancePeriod;
use App\Models\User;

class AttendancePeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendancePeriod $attendancePeriod): bool
    {
        return $user->school_id === $attendancePeriod->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, AttendancePeriod $attendancePeriod): bool
    {
        return $user->school_id === $attendancePeriod->school_id && $user->hasPermission('attendance.manage');
    }

    public function delete(User $user, AttendancePeriod $attendancePeriod): bool
    {
        return $user->school_id === $attendancePeriod->school_id && $user->hasPermission('attendance.manage');
    }
}
