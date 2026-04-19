<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\StudentData;
use App\Events\SIS\StudentCreated;
use App\Models\Student;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(
        protected StudentRepositoryInterface $students,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->students->paginate($filters, $perPage);
    }

    public function create(StudentData $data): Student
    {
        return DB::transaction(function () use ($data): Student {
            $student = $this->students->create($data);
            event(new StudentCreated($student));

            return $student;
        });
    }

    public function update(Student $student, StudentData $data): Student
    {
        return DB::transaction(fn (): Student => $this->students->update($student, $data));
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student): void {
            $this->students->delete($student);
        });
    }
}
