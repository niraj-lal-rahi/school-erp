<?php

namespace App\Services\Attendance;

use App\DataTransferObjects\Attendance\StudentAttendanceSessionData;
use App\Enums\Attendance\AttendanceSessionStatus;
use App\Events\Attendance\StudentAttendanceSessionLocked;
use App\Events\Attendance\StudentAttendanceSessionSubmitted;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\StudentEnrollment;
use App\Repositories\Contracts\Attendance\StudentAttendanceRecordRepositoryInterface;
use App\Repositories\Contracts\Attendance\StudentAttendanceSessionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentAttendanceSessionService
{
    public function __construct(
        protected StudentAttendanceSessionRepositoryInterface $sessions,
        protected StudentAttendanceRecordRepositoryInterface $records,
        protected AttendanceHolidayService $holidays,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->sessions->all($filters);
    }

    public function create(StudentAttendanceSessionData $data): StudentAttendanceSession
    {
        return DB::transaction(function () use ($data): StudentAttendanceSession {
            $attributes = $this->withSessionSlot($data->attributes);
            $this->validateTeacherAssignment($attributes);
            $this->validateDuplicateSession($attributes);
            $this->guardAgainstHoliday($attributes);

            return $this->sessions->refreshWithRelations(
                $this->sessions->create(StudentAttendanceSessionData::fromArray($attributes))
            );
        });
    }

    public function update(StudentAttendanceSession $session, StudentAttendanceSessionData $data): StudentAttendanceSession
    {
        $this->ensureSessionEditable($session);

        return DB::transaction(function () use ($session, $data): StudentAttendanceSession {
            $attributes = $this->withSessionSlot($data->attributes);
            $this->validateTeacherAssignment($attributes);
            $this->validateDuplicateSession($attributes, $session->id);
            $this->guardAgainstHoliday($attributes);

            return $this->sessions->update($session, StudentAttendanceSessionData::fromArray($attributes));
        });
    }

    public function delete(StudentAttendanceSession $session): void
    {
        $this->ensureSessionEditable($session);

        DB::transaction(function () use ($session): void {
            $this->sessions->delete($session);
        });
    }

    public function bulkMark(StudentAttendanceSession $session, array $records, int $markedBy): StudentAttendanceSession
    {
        $this->ensureSessionEditable($session);

        return DB::transaction(function () use ($session, $records, $markedBy): StudentAttendanceSession {
            $validStudentIds = StudentEnrollment::query()
                ->where('academic_year_id', $session->academic_year_id)
                ->where('school_class_id', $session->school_class_id)
                ->where('section_id', $session->section_id)
                ->where('is_current', true)
                ->pluck('student_id')
                ->all();

            $validStatusIds = AttendanceStatusType::query()
                ->where('status', 'active')
                ->pluck('id')
                ->all();

            foreach ($records as $record) {
                if (! in_array($record['student_id'], $validStudentIds, true)) {
                    throw ValidationException::withMessages([
                        'records' => ["Student {$record['student_id']} is not enrolled in the selected class and section."],
                    ]);
                }

                if (! in_array($record['attendance_status_type_id'], $validStatusIds, true)) {
                    throw ValidationException::withMessages([
                        'records' => ["Attendance status {$record['attendance_status_type_id']} is not valid for this tenant."],
                    ]);
                }
            }

            $this->records->upsertForSession($session, $records, $session->school_id, $markedBy);

            return $this->sessions->refreshWithRelations($session);
        });
    }

    public function submit(StudentAttendanceSession $session): StudentAttendanceSession
    {
        $this->ensureSessionEditable($session);

        return DB::transaction(function () use ($session): StudentAttendanceSession {
            $updated = $this->sessions->update($session, StudentAttendanceSessionData::fromArray([
                'school_id' => $session->school_id,
                'academic_year_id' => $session->academic_year_id,
                'school_class_id' => $session->school_class_id,
                'section_id' => $session->section_id,
                'attendance_date' => $session->attendance_date?->toDateString(),
                'session_type' => $session->session_type,
                'attendance_period_id' => $session->attendance_period_id,
                'session_slot' => $session->session_slot,
                'subject_id' => $session->subject_id,
                'teacher_id' => $session->teacher_id,
                'status' => AttendanceSessionStatus::Submitted->value,
                'marked_by' => $session->marked_by,
                'submitted_at' => now(),
                'locked_at' => null,
            ]));

            event(new StudentAttendanceSessionSubmitted($updated));

            return $updated;
        });
    }

    public function lock(StudentAttendanceSession $session): StudentAttendanceSession
    {
        if ($session->status === AttendanceSessionStatus::Locked->value) {
            return $this->sessions->refreshWithRelations($session);
        }

        return DB::transaction(function () use ($session): StudentAttendanceSession {
            $updated = $this->sessions->update($session, StudentAttendanceSessionData::fromArray([
                'school_id' => $session->school_id,
                'academic_year_id' => $session->academic_year_id,
                'school_class_id' => $session->school_class_id,
                'section_id' => $session->section_id,
                'attendance_date' => $session->attendance_date?->toDateString(),
                'session_type' => $session->session_type,
                'attendance_period_id' => $session->attendance_period_id,
                'session_slot' => $session->session_slot,
                'subject_id' => $session->subject_id,
                'teacher_id' => $session->teacher_id,
                'status' => AttendanceSessionStatus::Locked->value,
                'marked_by' => $session->marked_by,
                'submitted_at' => $session->submitted_at ?? now(),
                'locked_at' => now(),
            ]));

            event(new StudentAttendanceSessionLocked($updated));

            return $updated;
        });
    }

    public function ensureSessionEditable(StudentAttendanceSession $session): void
    {
        if ($session->status === AttendanceSessionStatus::Locked->value) {
            throw ValidationException::withMessages([
                'session' => ['Locked attendance sessions cannot be modified.'],
            ]);
        }
    }

    protected function validateTeacherAssignment(array $attributes): void
    {
        if (! ($attributes['teacher_id'] ?? null) || ! ($attributes['subject_id'] ?? null)) {
            return;
        }

        $exists = TeacherAssignment::query()
            ->where('academic_year_id', $attributes['academic_year_id'])
            ->where('school_class_id', $attributes['school_class_id'])
            ->where('section_id', $attributes['section_id'])
            ->where('subject_id', $attributes['subject_id'])
            ->where('staff_id', $attributes['teacher_id'])
            ->where('status', 'active')
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'teacher_id' => ['The selected teacher is not assigned to this class, section, and subject.'],
            ]);
        }
    }

    protected function withSessionSlot(array $attributes): array
    {
        $attributes['session_slot'] = ($attributes['session_type'] ?? 'daily') === 'daily'
            ? 'daily'
            : 'period:'.($attributes['attendance_period_id'] ?? 'unknown');

        return $attributes;
    }

    protected function validateDuplicateSession(array $attributes, ?int $ignoreId = null): void
    {
        $exists = StudentAttendanceSession::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('academic_year_id', $attributes['academic_year_id'])
            ->where('school_class_id', $attributes['school_class_id'])
            ->where('section_id', $attributes['section_id'])
            ->whereDate('attendance_date', $attributes['attendance_date'])
            ->where('session_slot', $attributes['session_slot'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'attendance_date' => ['An attendance session already exists for this class, section, date, and slot.'],
            ]);
        }
    }

    protected function guardAgainstHoliday(array $attributes): void
    {
        $isHoliday = $this->holidays->isHolidayForStudents(
            schoolId: (int) $attributes['school_id'],
            academicYearId: (int) $attributes['academic_year_id'],
            date: (string) $attributes['attendance_date'],
            classId: $attributes['school_class_id'] ?? null,
            sectionId: $attributes['section_id'] ?? null,
        );

        if ($isHoliday) {
            throw ValidationException::withMessages([
                'attendance_date' => ['Attendance cannot be marked on a holiday for the selected scope.'],
            ]);
        }
    }
}
