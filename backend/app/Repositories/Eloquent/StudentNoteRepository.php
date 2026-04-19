<?php

namespace App\Repositories\Eloquent;

use App\DataTransferObjects\SIS\StudentNoteData;
use App\Models\Student;
use App\Models\StudentNote;
use App\Repositories\Contracts\StudentNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentNoteRepository implements StudentNoteRepositoryInterface
{
    public function create(Student $student, StudentNoteData $data): StudentNote
    {
        return $student->notesEntries()->create($data->attributes);
    }

    public function update(StudentNote $note, StudentNoteData $data): StudentNote
    {
        $note->update($data->attributes);

        return $note->refresh()->load('creator');
    }

    public function delete(StudentNote $note): void
    {
        $note->delete();
    }

    public function allForStudent(Student $student): Collection
    {
        return StudentNote::query()
            ->with('creator')
            ->where('student_id', $student->id)
            ->latest('id')
            ->get();
    }
}
