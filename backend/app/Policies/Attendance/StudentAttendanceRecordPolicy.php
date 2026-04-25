<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\User;

class StudentAttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, StudentAttendanceRecord $record): bool
    {
        return $user->school_id === $record->school_id && $user->hasPermission('attendance.view');
    }

    public function update(User $user, StudentAttendanceRecord $record): bool
    {
        return $user->school_id === $record->school_id && $user->hasPermission('attendance.manage');
    }

    public function delete(User $user, StudentAttendanceRecord $record): bool
    {
        return $user->school_id === $record->school_id && $user->hasPermission('attendance.manage');
    }
}
