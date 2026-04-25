<?php

namespace App\Policies\Attendance;

use App\Models\Attendance\AttendanceImport;
use App\Models\User;

class AttendanceImportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('attendance.view');
    }

    public function view(User $user, AttendanceImport $attendanceImport): bool
    {
        return $user->school_id === $attendanceImport->school_id && $user->hasPermission('attendance.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('attendance.manage');
    }

    public function update(User $user, AttendanceImport $attendanceImport): bool
    {
        return $user->school_id === $attendanceImport->school_id && $user->hasPermission('attendance.manage');
    }
}
