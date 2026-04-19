<?php

namespace App\Services\SIS;

use App\DataTransferObjects\SIS\EnrollmentData;
use App\DataTransferObjects\SIS\StudentStatusActionData;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentStatusHistory;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use App\Repositories\Contracts\StudentStatusHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StudentLifecycleService
{
    public function __construct(
        protected StudentEnrollmentRepositoryInterface $enrollments,
        protected StudentStatusHistoryRepositoryInterface $history,
    ) {
    }

    public function statusHistory(Student $student): Collection
    {
        return $this->history->allForStudent($student);
    }

    public function promote(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return DB::transaction(function () use ($student, $data, $performedBy): Student {
            $currentEnrollment = $this->enrollments->currentForStudent($student);

            if ($currentEnrollment) {
                $this->enrollments->update($currentEnrollment, EnrollmentData::fromArray([
                    'student_id' => $currentEnrollment->student_id,
                    'academic_year_id' => $currentEnrollment->academic_year_id,
                    'school_class_id' => $currentEnrollment->school_class_id,
                    'section_id' => $currentEnrollment->section_id,
                    'roll_number' => $currentEnrollment->roll_number,
                    'enrollment_date' => optional($currentEnrollment->enrollment_date)->toDateString(),
                    'joined_on' => optional($currentEnrollment->joined_on)->toDateString(),
                    'ended_on' => $data->attributes['effective_date'],
                    'status' => 'promoted',
                    'is_current' => false,
                    'remarks' => $data->attributes['remarks'] ?? $data->attributes['reason'] ?? null,
                ]));
            }

            $this->enrollments->create(EnrollmentData::fromArray([
                'school_id' => $student->school_id,
                'student_id' => $student->id,
                'academic_year_id' => $data->attributes['academic_year_id'],
                'school_class_id' => $data->attributes['school_class_id'],
                'section_id' => $data->attributes['section_id'] ?? null,
                'roll_number' => $data->attributes['roll_number'] ?? null,
                'enrollment_date' => $data->attributes['enrollment_date'] ?? $data->attributes['effective_date'],
                'joined_on' => $data->attributes['joined_on'] ?? $data->attributes['effective_date'],
                'status' => 'enrolled',
                'is_current' => true,
                'remarks' => $data->attributes['remarks'] ?? null,
            ]));

            $student->update([
                'current_status' => 'active',
                'status' => 'active',
            ]);

            $this->recordHistory($student, 'promoted', 'active', $data, $performedBy);

            return $student->refresh();
        });
    }

    public function transferSection(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return DB::transaction(function () use ($student, $data, $performedBy): Student {
            $currentEnrollment = $this->requireCurrentEnrollment($student);

            $this->enrollments->update($currentEnrollment, EnrollmentData::fromArray([
                'student_id' => $currentEnrollment->student_id,
                'academic_year_id' => $currentEnrollment->academic_year_id,
                'school_class_id' => $currentEnrollment->school_class_id,
                'section_id' => $data->attributes['section_id'] ?? $currentEnrollment->section_id,
                'roll_number' => $data->attributes['roll_number'] ?? $currentEnrollment->roll_number,
                'enrollment_date' => optional($currentEnrollment->enrollment_date)->toDateString(),
                'joined_on' => optional($currentEnrollment->joined_on)->toDateString(),
                'status' => 'transferred',
                'is_current' => true,
                'remarks' => $data->attributes['remarks'] ?? $data->attributes['reason'] ?? null,
            ]));

            $this->recordHistory($student, 'transfer_section', $student->current_status, $data, $performedBy);

            return $student->refresh();
        });
    }

    public function withdraw(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return $this->transitionStatus($student, 'withdrawn', 'withdraw', $data, $performedBy, 'withdrawn');
    }

    public function graduate(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return $this->transitionStatus($student, 'graduated', 'graduate', $data, $performedBy, 'completed');
    }

    public function suspend(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return $this->transitionStatus($student, 'suspended', 'suspend', $data, $performedBy);
    }

    public function reactivate(Student $student, StudentStatusActionData $data, int $performedBy): Student
    {
        return $this->transitionStatus($student, 'active', 'reactivate', $data, $performedBy, 'enrolled');
    }

    protected function transitionStatus(
        Student $student,
        string $newStatus,
        string $actionType,
        StudentStatusActionData $data,
        int $performedBy,
        ?string $enrollmentStatus = null,
    ): Student {
        return DB::transaction(function () use ($student, $newStatus, $actionType, $data, $performedBy, $enrollmentStatus): Student {
            $currentEnrollment = $this->enrollments->currentForStudent($student);

            if ($currentEnrollment && $enrollmentStatus !== null) {
                $this->enrollments->update($currentEnrollment, EnrollmentData::fromArray([
                    'student_id' => $currentEnrollment->student_id,
                    'academic_year_id' => $currentEnrollment->academic_year_id,
                    'school_class_id' => $currentEnrollment->school_class_id,
                    'section_id' => $currentEnrollment->section_id,
                    'roll_number' => $currentEnrollment->roll_number,
                    'enrollment_date' => optional($currentEnrollment->enrollment_date)->toDateString(),
                    'joined_on' => optional($currentEnrollment->joined_on)->toDateString(),
                    'ended_on' => in_array($newStatus, ['withdrawn', 'graduated'], true) ? $data->attributes['effective_date'] : optional($currentEnrollment->ended_on)->toDateString(),
                    'status' => $enrollmentStatus,
                    'is_current' => ! in_array($newStatus, ['withdrawn', 'graduated'], true),
                    'remarks' => $data->attributes['remarks'] ?? $data->attributes['reason'] ?? null,
                ]));
            }

            $previousStatus = $student->current_status;
            $student->update([
                'current_status' => $newStatus,
                'status' => $newStatus,
            ]);

            $this->history->create($student, [
                'school_id' => $student->school_id,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'action_type' => $actionType,
                'reason' => $data->attributes['reason'] ?? $data->attributes['remarks'] ?? null,
                'effective_date' => $data->attributes['effective_date'],
                'performed_by' => $performedBy,
            ]);

            return $student->refresh();
        });
    }

    protected function recordHistory(Student $student, string $actionType, string $newStatus, StudentStatusActionData $data, int $performedBy): StudentStatusHistory
    {
        return $this->history->create($student, [
            'school_id' => $student->school_id,
            'previous_status' => $student->getOriginal('current_status'),
            'new_status' => $newStatus,
            'action_type' => $actionType,
            'reason' => $data->attributes['reason'] ?? $data->attributes['remarks'] ?? null,
            'effective_date' => $data->attributes['effective_date'],
            'performed_by' => $performedBy,
        ]);
    }

    protected function requireCurrentEnrollment(Student $student): StudentEnrollment
    {
        $enrollment = $this->enrollments->currentForStudent($student);

        abort_if(! $enrollment, 422, 'Student does not have a current enrollment.');

        return $enrollment;
    }
}
