<?php

namespace App\Policies;

use App\Models\StudentNote;
use App\Models\User;

class StudentNotePolicy
{
    public function view(User $user, StudentNote $note): bool
    {
        return $user->school_id === $note->school_id && $user->hasPermission('students.view');
    }

    public function update(User $user, StudentNote $note): bool
    {
        return $user->school_id === $note->school_id && $user->hasPermission('students.update');
    }

    public function delete(User $user, StudentNote $note): bool
    {
        return $this->update($user, $note);
    }
}
