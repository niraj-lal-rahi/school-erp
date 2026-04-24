<?php

namespace App\Services\Finance;

use App\DataTransferObjects\Finance\StudentFeeAssignmentData;
use App\Enums\Finance\FeeAssignmentStatus;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Student;
use App\Repositories\Contracts\Finance\StudentFeeAssignmentRepositoryInterface;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentFeeAssignmentService
{
    public function __construct(
        protected StudentFeeAssignmentRepositoryInterface $assignments,
        protected StudentEnrollmentRepositoryInterface $enrollments,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->assignments->paginate($filters, $perPage);
    }

    public function create(StudentFeeAssignmentData $data): StudentFeeAssignment
    {
        $this->guardAssignmentValidity($data->attributes);

        return DB::transaction(fn (): StudentFeeAssignment => $this->assignments->create($data));
    }

    public function update(StudentFeeAssignment $assignment, StudentFeeAssignmentData $data): StudentFeeAssignment
    {
        $this->guardAssignmentValidity($data->attributes, $assignment);

        return DB::transaction(fn (): StudentFeeAssignment => $this->assignments->update($assignment, $data));
    }

    public function delete(StudentFeeAssignment $assignment): void
    {
        DB::transaction(fn (): bool => $assignment->delete());
    }

    public function assignStructureToStudent(Student $student, FeeStructure $feeStructure, array $payload): StudentFeeAssignment
    {
        $currentEnrollment = $this->enrollments->currentForStudent($student);

        if (! $currentEnrollment) {
            throw ValidationException::withMessages([
                'student_id' => 'The selected student does not have a current enrollment.',
            ]);
        }

        $attributes = [
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'academic_year_id' => $currentEnrollment->academic_year_id,
            'school_class_id' => $currentEnrollment->school_class_id,
            'section_id' => $currentEnrollment->section_id,
            'fee_structure_id' => $feeStructure->id,
            'assigned_date' => $payload['assigned_date'] ?? now()->toDateString(),
            'status' => $payload['status'] ?? FeeAssignmentStatus::Active->value,
            'remarks' => $payload['remarks'] ?? null,
        ];

        $this->guardFeeStructureMatchesPlacement($feeStructure, $attributes);
        $this->guardAssignmentValidity($attributes);

        return DB::transaction(fn (): StudentFeeAssignment => $this->assignments->create(StudentFeeAssignmentData::fromArray($attributes)));
    }

    protected function guardAssignmentValidity(array $attributes, ?StudentFeeAssignment $assignment = null): void
    {
        $student = Student::query()->findOrFail($attributes['student_id']);
        $ignoreId = $assignment?->id;

        if (($attributes['status'] ?? null) === FeeAssignmentStatus::Active->value) {
            $existing = $this->assignments->activeForStudentAcademicYear($student, (int) $attributes['academic_year_id'], $ignoreId);
            if ($existing) {
                throw ValidationException::withMessages([
                    'student_id' => 'An active fee assignment already exists for this student in the selected academic year.',
                ]);
            }
        }

        if (($attributes['section_id'] ?? null) && ($attributes['school_class_id'] ?? null)) {
            $section = \App\Models\Section::query()->find($attributes['section_id']);
            if (! $section || (int) $section->school_class_id !== (int) $attributes['school_class_id']) {
                throw ValidationException::withMessages([
                    'section_id' => 'The selected section does not belong to the selected class.',
                ]);
            }
        }

        $feeStructure = FeeStructure::query()->findOrFail($attributes['fee_structure_id']);
        $this->guardFeeStructureMatchesPlacement($feeStructure, $attributes);
    }

    protected function guardFeeStructureMatchesPlacement(FeeStructure $feeStructure, array $attributes): void
    {
        if ((int) $feeStructure->academic_year_id !== (int) $attributes['academic_year_id']) {
            throw ValidationException::withMessages([
                'fee_structure_id' => 'The selected fee structure belongs to a different academic year.',
            ]);
        }

        if ($feeStructure->school_class_id && (int) $feeStructure->school_class_id !== (int) $attributes['school_class_id']) {
            throw ValidationException::withMessages([
                'fee_structure_id' => 'The selected fee structure is not valid for the selected class.',
            ]);
        }

        if ($feeStructure->section_id && (int) $feeStructure->section_id !== (int) ($attributes['section_id'] ?? 0)) {
            throw ValidationException::withMessages([
                'fee_structure_id' => 'The selected fee structure is not valid for the selected section.',
            ]);
        }
    }
}
