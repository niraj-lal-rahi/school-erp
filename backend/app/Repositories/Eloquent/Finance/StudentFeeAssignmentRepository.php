<?php

namespace App\Repositories\Eloquent\Finance;

use App\DataTransferObjects\Finance\StudentFeeAssignmentData;
use App\Enums\Finance\FeeAssignmentStatus;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Student;
use App\Repositories\Contracts\Finance\StudentFeeAssignmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StudentFeeAssignmentRepository implements StudentFeeAssignmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $assignmentQuery) use ($search): void {
                    $assignmentQuery->whereHas('student', function (Builder $studentQuery) use ($search): void {
                        $studentQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('admission_no', 'like', "%{$search}%");
                    })->orWhereHas('feeStructure', function (Builder $structureQuery) use ($search): void {
                        $structureQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['student_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('student_id', $value))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('academic_year_id', $value))
            ->when($filters['school_class_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('school_class_id', $value))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('section_id', $value))
            ->when($filters['fee_structure_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('fee_structure_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function create(StudentFeeAssignmentData $data): StudentFeeAssignment
    {
        $assignment = StudentFeeAssignment::create($data->attributes);

        return $this->findOrFail($assignment->id);
    }

    public function update(StudentFeeAssignment $assignment, StudentFeeAssignmentData $data): StudentFeeAssignment
    {
        $assignment->update($data->attributes);

        return $this->findOrFail($assignment->id);
    }

    public function delete(StudentFeeAssignment $assignment): void
    {
        $assignment->delete();
    }

    public function activeForStudentAcademicYear(Student $student, int $academicYearId, ?int $ignoreId = null): ?StudentFeeAssignment
    {
        return $this->query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('status', FeeAssignmentStatus::Active->value)
            ->when($ignoreId, fn (Builder $query, int $value) => $query->whereKeyNot($value))
            ->first();
    }

    protected function query(): Builder
    {
        return StudentFeeAssignment::query()->with(['student', 'academicYear', 'schoolClass', 'section', 'feeStructure']);
    }

    protected function findOrFail(int $id): StudentFeeAssignment
    {
        return $this->query()->findOrFail($id);
    }
}
