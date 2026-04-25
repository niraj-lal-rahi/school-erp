<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendanceStatusType;
use App\Models\User;

class AttendanceStatusTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceStatusType $attendanceStatusType): bool
    {
        return $user->school_id === $attendanceStatusType->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, AttendanceStatusType $attendanceStatusType): bool
    {
        return $user->school_id === $attendanceStatusType->school_id && $user->hasPermission('attendance.manage');
    }

    public function delete(User $user, AttendanceStatusType $attendanceStatusType): bool
    {
        return $user->school_id === $attendanceStatusType->school_id && $user->hasPermission('attendance.manage');
    }
}
