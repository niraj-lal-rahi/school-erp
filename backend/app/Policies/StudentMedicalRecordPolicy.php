<?php

namespace App\Policies;

use App\Models\StudentMedicalRecord;
use App\Models\User;

class StudentMedicalRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function view(User $user, StudentMedicalRecord $record): bool
    {
        return $user->school_id === $record->school_id && $user->hasPermission('students.view');
    }

    public function update(User $user, StudentMedicalRecord $record): bool
    {
        return $user->school_id === $record->school_id && $user->hasPermission('students.medical.manage');
    }

    public function delete(User $user, StudentMedicalRecord $record): bool
    {
        return $this->update($user, $record);
    }
}
