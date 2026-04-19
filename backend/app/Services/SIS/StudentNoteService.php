<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentNoteData;
use App\Models\Student;
use App\Models\StudentNote;
use App\Repositories\Contracts\StudentNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentNoteService
{
    public function __construct(
        protected StudentNoteRepositoryInterface $notes,
    ) {
    }

    public function allForStudent(Student $student): Collection
    {
        return $this->notes->allForStudent($student);
    }

    public function create(Student $student, StudentNoteData $data, int $performedBy): StudentNote
    {
        return DB::transaction(fn (): StudentNote => $this->notes->create($student, StudentNoteData::fromArray([
            ...$data->attributes,
            'school_id' => $student->school_id,
            'created_by' => $performedBy,
        ])));
    }

    public function update(StudentNote $note, StudentNoteData $data): StudentNote
    {
        return DB::transaction(fn (): StudentNote => $this->notes->update($note, $data));
    }

    public function delete(StudentNote $note): void
    {
        DB::transaction(fn (): bool => $note->delete());
    }
}
