<?php

namespace App\Services\Timetable;

use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Section;
use App\Models\Timetable\TimetableVersion;
use App\Repositories\Contracts\Timetable\TimetableEntryRepositoryInterface;
use Illuminate\Validation\ValidationException;

class TimetableConflictService
{
    public function __construct(
        protected TimetableEntryRepositoryInterface $entries,
    ) {
    }

    public function validateEntry(array $attributes, ?int $ignoreEntryId = null): array
    {
        $version = TimetableVersion::query()->findOrFail($attributes['timetable_version_id']);

        if ($version->status !== 'draft') {
            throw ValidationException::withMessages([
                'timetable_version_id' => ['Only draft timetable versions can be edited.'],
            ]);
        }

        $this->ensureSectionBelongsToClass((int) $attributes['school_class_id'], (int) $attributes['section_id']);

        if (($attributes['entry_type'] ?? 'class') === 'class') {
            $this->ensureClassSubjectAssignmentExists($attributes);

            if (! empty($attributes['staff_id'])) {
                $this->ensureTeacherAssignmentExists($attributes);
            }
        }

        $slotConflict = $this->entries->findBySlot(
            (int) $attributes['school_id'],
            (int) $attributes['timetable_version_id'],
            (int) $attributes['school_class_id'],
            (int) $attributes['section_id'],
            (string) $attributes['day_of_week'],
            (int) $attributes['attendance_period_id'],
            $ignoreEntryId,
        );

        $conflicts = [];

        if ($slotConflict) {
            $conflicts[] = [
                'type' => 'class_slot',
                'message' => 'This class section already has an entry for the selected day and period.',
                'conflicting_entry_id' => $slotConflict->id,
            ];
        }

        if (! empty($attributes['staff_id'])) {
            $teacherConflict = $this->entries->findTeacherConflict(
                (int) $attributes['school_id'],
                (int) $attributes['timetable_version_id'],
                (int) $attributes['staff_id'],
                (string) $attributes['day_of_week'],
                (int) $attributes['attendance_period_id'],
                $ignoreEntryId,
            );

            if ($teacherConflict) {
                $conflicts[] = [
                    'type' => 'teacher',
                    'message' => 'The selected teacher is already assigned during this day and period.',
                    'conflicting_entry_id' => $teacherConflict->id,
                ];
            }
        }

        if (! empty($attributes['room_id'])) {
            $roomConflict = $this->entries->findRoomConflict(
                (int) $attributes['school_id'],
                (int) $attributes['timetable_version_id'],
                (int) $attributes['room_id'],
                (string) $attributes['day_of_week'],
                (int) $attributes['attendance_period_id'],
                $ignoreEntryId,
            );

            if ($roomConflict) {
                $conflicts[] = [
                    'type' => 'room',
                    'message' => 'The selected room is already booked during this day and period.',
                    'conflicting_entry_id' => $roomConflict->id,
                ];
            }
        }

        return $conflicts;
    }

    protected function ensureSectionBelongsToClass(int $classId, int $sectionId): void
    {
        $exists = Section::query()
            ->whereKey($sectionId)
            ->where('school_class_id', $classId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'section_id' => ['The selected section does not belong to the chosen class.'],
            ]);
        }
    }

    protected function ensureClassSubjectAssignmentExists(array $attributes): void
    {
        $exists = ClassSubjectAssignment::query()
            ->where('academic_year_id', $attributes['academic_year_id'])
            ->where('school_class_id', $attributes['school_class_id'])
            ->where('subject_id', $attributes['subject_id'])
            ->where(function ($query) use ($attributes): void {
                $query->where('section_id', $attributes['section_id'])
                    ->orWhereNull('section_id');
            })
            ->where('status', 'active')
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'subject_id' => ['The selected subject is not assigned to this class or section in the academic module.'],
            ]);
        }
    }

    protected function ensureTeacherAssignmentExists(array $attributes): void
    {
        $exists = TeacherAssignment::query()
            ->where('academic_year_id', $attributes['academic_year_id'])
            ->where('school_class_id', $attributes['school_class_id'])
            ->where('subject_id', $attributes['subject_id'])
            ->where('staff_id', $attributes['staff_id'])
            ->where(function ($query) use ($attributes): void {
                $query->where('section_id', $attributes['section_id'])
                    ->orWhereNull('section_id');
            })
            ->where('status', 'active')
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'staff_id' => ['The selected teacher is not assigned to this class, section, and subject combination.'],
            ]);
        }
    }
}
