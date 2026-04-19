<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function view(User $user, Student $student): bool
    {
        return $user->school_id === $student->school_id && $user->hasPermission('students.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->school_id === $student->school_id && $user->hasPermission('students.update');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->school_id === $student->school_id && $user->hasPermission('students.delete');
    }

    public function uploadDocument(User $user, Student $student): bool
    {
        return $user->school_id === $student->school_id && $user->hasPermission('students.documents.upload');
    }

    public function viewDocuments(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function viewMedical(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function updateMedical(User $user, Student $student): bool
    {
        return $user->school_id === $student->school_id && $user->hasPermission('students.medical.manage');
    }

    public function manageLifecycle(User $user, Student $student): bool
    {
        return $this->update($user, $student);
    }

    public function manageNotes(User $user, Student $student): bool
    {
        return $this->update($user, $student);
    }

    public function assignGuardian(User $user, Student $student): bool
    {
        return $this->update($user, $student);
    }

    public function removeGuardian(User $user, Student $student): bool
    {
        return $this->update($user, $student);
    }
}
