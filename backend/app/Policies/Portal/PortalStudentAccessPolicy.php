<?php

namespace App\Policies\Portal;

use App\Models\Portal\PortalProfileAccess;
use App\Models\User;

class PortalStudentAccessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('portal.view') || $user->hasPermission('portal.manage');
    }

    public function view(User $user, PortalProfileAccess $access): bool
    {
        return $user->school_id === $access->school_id
            && ($user->hasPermission('portal.view') || $user->hasPermission('portal.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('portal.manage');
    }

    public function update(User $user, PortalProfileAccess $access): bool
    {
        return $user->school_id === $access->school_id
            && $user->hasPermission('portal.manage');
    }

    public function delete(User $user, PortalProfileAccess $access): bool
    {
        return $user->school_id === $access->school_id
            && $user->hasPermission('portal.manage');
    }

    public function viewStudent(User $user, int $studentId): bool
    {
        if (! ($user->hasPermission('portal.view') || $user->hasPermission('portal.manage'))) {
            return false;
        }

        return PortalProfileAccess::query()
            ->where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();
    }

    public function viewAttendance(User $user, int $studentId): bool
    {
        return $this->hasStudentPermission($user, $studentId, 'can_view_attendance');
    }

    public function viewFees(User $user, int $studentId): bool
    {
        return $this->hasStudentPermission($user, $studentId, 'can_view_fees');
    }

    public function viewResults(User $user, int $studentId): bool
    {
        return $this->hasStudentPermission($user, $studentId, 'can_view_results');
    }

    public function viewDocuments(User $user, int $studentId): bool
    {
        return $this->hasStudentPermission($user, $studentId, 'can_view_documents');
    }

    public function viewAssignments(User $user, int $studentId): bool
    {
        return $this->viewStudent($user, $studentId);
    }

    public function viewTransport(User $user, int $studentId): bool
    {
        return $this->viewStudent($user, $studentId);
    }

    public function viewTimetable(User $user, int $studentId): bool
    {
        return $this->viewStudent($user, $studentId);
    }

    protected function hasStudentPermission(User $user, int $studentId, string $column): bool
    {
        if (! ($user->hasPermission('portal.view') || $user->hasPermission('portal.manage'))) {
            return false;
        }

        return PortalProfileAccess::query()
            ->where('user_id', $user->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->where($column, true)
            ->exists();
    }
}
