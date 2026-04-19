<?php

namespace App\Policies;

use App\Models\StudentDocument;
use App\Models\User;

class StudentDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('students.view');
    }

    public function view(User $user, StudentDocument $document): bool
    {
        return $user->school_id === $document->school_id && $user->hasPermission('students.view');
    }

    public function update(User $user, StudentDocument $document): bool
    {
        return $user->school_id === $document->school_id && $user->hasPermission('students.documents.upload');
    }

    public function delete(User $user, StudentDocument $document): bool
    {
        return $this->update($user, $document);
    }
}
