<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\EnrollmentData;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentEnrollmentService
{
    public function __construct(
        protected StudentEnrollmentRepositoryInterface $enrollments,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->enrollments->paginate($filters, $perPage);
    }

    public function show(StudentEnrollment $enrollment): StudentEnrollment
    {
        return $this->enrollments->findOrFail($enrollment->id);
    }

    public function create(EnrollmentData $data): StudentEnrollment
    {
        return DB::transaction(function () use ($data): StudentEnrollment {
            $this->deactivateCurrentIfNeeded($data->attributes['student_id'], (bool) ($data->attributes['is_current'] ?? true));

            return $this->enrollments->create($data);
        });
    }

    public function update(StudentEnrollment $enrollment, EnrollmentData $data): StudentEnrollment
    {
        return DB::transaction(function () use ($enrollment, $data): StudentEnrollment {
            $this->deactivateCurrentIfNeeded(
                $data->attributes['student_id'] ?? $enrollment->student_id,
                (bool) ($data->attributes['is_current'] ?? $enrollment->is_current),
                $enrollment->id,
            );

            return $this->enrollments->update($enrollment, $data);
        });
    }

    public function delete(StudentEnrollment $enrollment): void
    {
        DB::transaction(function () use ($enrollment): void {
            $this->enrollments->delete($enrollment);
        });
    }

    public function enrollStudent(Student $student, array $payload): StudentEnrollment
    {
        return $this->create(EnrollmentData::fromArray([
            ...$payload,
            'school_id' => $student->school_id,
            'student_id' => $student->id,
        ]));
    }

    public function allForStudent(Student $student): Collection
    {
        return $this->enrollments->allForStudent($student);
    }

    protected function deactivateCurrentIfNeeded(int $studentId, bool $isCurrent, ?int $ignoreId = null): void
    {
        if (! $isCurrent) {
            return;
        }

        StudentEnrollment::query()
            ->where('student_id', $studentId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'status' => DB::raw("case when status = 'enrolled' then 'completed' else status end"),
            ]);
    }
}
