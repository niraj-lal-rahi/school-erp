<?php

namespace App\Repositories\Contracts;

use App\DataTransferObjects\SIS\StudentNoteData;
use App\Models\Student;
use App\Models\StudentNote;
use Illuminate\Database\Eloquent\Collection;

interface StudentNoteRepositoryInterface
{
    public function create(Student $student, StudentNoteData $data): StudentNote;

    public function update(StudentNote $note, StudentNoteData $data): StudentNote;

    public function delete(StudentNote $note): void;

    public function allForStudent(Student $student): Collection;
}
