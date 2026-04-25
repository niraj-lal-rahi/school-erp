<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetableSubstitutionData;
use App\Models\HR\Staff;
use App\Models\Timetable\TimetableEntry;
use App\Models\Timetable\TimetableSubstitution;
use App\Repositories\Contracts\Timetable\TimetableEntryRepositoryInterface;
use App\Repositories\Contracts\Timetable\TimetableSubstitutionRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableSubstitutionService
{
    public function __construct(
        protected TimetableSubstitutionRepositoryInterface $substitutions,
        protected TimetableEntryRepositoryInterface $entries,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->substitutions->all($filters);
    }

    public function create(TimetableSubstitutionData $data): TimetableSubstitution
    {
        return DB::transaction(function () use ($data): TimetableSubstitution {
            $this->validateSubstitution($data->attributes);

            return $this->substitutions->create($data);
        });
    }

    public function update(TimetableSubstitution $substitution, TimetableSubstitutionData $data): TimetableSubstitution
    {
        return DB::transaction(function () use ($substitution, $data): TimetableSubstitution {
            if ($substitution->status === 'completed') {
                throw ValidationException::withMessages([
                    'status' => ['Completed substitutions cannot be edited.'],
                ]);
            }

            $this->validateSubstitution($data->attributes, $substitution->id);

            return $this->substitutions->update($substitution, $data);
        });
    }

    public function delete(TimetableSubstitution $substitution): void
    {
        DB::transaction(function () use ($substitution): void {
            if ($substitution->status === 'approved') {
                throw ValidationException::withMessages([
                    'status' => ['Approved substitutions should be cancelled instead of deleted.'],
                ]);
            }

            $this->substitutions->delete($substitution);
        });
    }

    public function approve(TimetableSubstitution $substitution, int $approvedBy): TimetableSubstitution
    {
        return DB::transaction(function () use ($substitution, $approvedBy): TimetableSubstitution {
            $attributes = [
                'timetable_entry_id' => $substitution->timetable_entry_id,
                'original_staff_id' => $substitution->original_staff_id,
                'substitute_staff_id' => $substitution->substitute_staff_id,
                'substitution_date' => $substitution->substitution_date?->toDateString(),
                'reason' => $substitution->reason,
                'status' => 'approved',
                'school_id' => $substitution->school_id,
            ];

            $this->validateSubstitution($attributes, $substitution->id);

            return $this->substitutions->update($substitution, TimetableSubstitutionData::fromArray([
                ...$attributes,
                'approved_by' => $approvedBy,
            ]));
        });
    }

    public function cancel(TimetableSubstitution $substitution): TimetableSubstitution
    {
        return DB::transaction(fn (): TimetableSubstitution => $this->substitutions->update(
            $substitution,
            TimetableSubstitutionData::fromArray([
                'school_id' => $substitution->school_id,
                'timetable_entry_id' => $substitution->timetable_entry_id,
                'original_staff_id' => $substitution->original_staff_id,
                'substitute_staff_id' => $substitution->substitute_staff_id,
                'substitution_date' => $substitution->substitution_date?->toDateString(),
                'reason' => $substitution->reason,
                'status' => 'cancelled',
                'approved_by' => $substitution->approved_by,
            ])
        ));
    }

    protected function validateSubstitution(array $attributes, ?int $ignoreSubstitutionId = null): void
    {
        /** @var TimetableEntry $entry */
        $entry = TimetableEntry::query()
            ->with(['timetableVersion', 'period'])
            ->findOrFail($attributes['timetable_entry_id']);

        $originalStaff = Staff::query()->findOrFail($attributes['original_staff_id']);
        $substituteStaff = Staff::query()->findOrFail($attributes['substitute_staff_id']);

        if ($originalStaff->school_id !== $entry->school_id || $substituteStaff->school_id !== $entry->school_id) {
            throw ValidationException::withMessages([
                'substitute_staff_id' => ['Staff selections must belong to the same tenant as the timetable entry.'],
            ]);
        }

        $substitutionDate = CarbonImmutable::parse($attributes['substitution_date']);
        $expectedDay = strtolower($substitutionDate->englishDayOfWeek);

        if ($entry->day_of_week !== $expectedDay) {
            throw ValidationException::withMessages([
                'substitution_date' => ['The substitution date must match the timetable entry day of week.'],
            ]);
        }

        if ((int) $attributes['original_staff_id'] !== (int) $entry->staff_id) {
            throw ValidationException::withMessages([
                'original_staff_id' => ['The original staff must match the timetable entry teacher.'],
            ]);
        }

        if ($entry->timetableVersion && $entry->timetableVersion->status === 'archived') {
            throw ValidationException::withMessages([
                'timetable_entry_id' => ['Archived timetable versions cannot receive substitutions.'],
            ]);
        }

        $teacherConflict = $this->entries->findTeacherConflict(
            $entry->school_id,
            $entry->timetable_version_id,
            (int) $attributes['substitute_staff_id'],
            $entry->day_of_week,
            $entry->attendance_period_id,
            $entry->id
        );

        if ($teacherConflict) {
            throw ValidationException::withMessages([
                'substitute_staff_id' => ['The substitute teacher already has a timetable conflict for this slot.'],
            ]);
        }

        $substitutionConflict = $this->substitutions->findSubstituteConflict(
            $entry->school_id,
            (int) $attributes['substitute_staff_id'],
            $entry->day_of_week,
            $entry->attendance_period_id,
            $substitutionDate->toDateString(),
            $ignoreSubstitutionId
        );

        if ($substitutionConflict) {
            throw ValidationException::withMessages([
                'substitute_staff_id' => ['The substitute teacher already has another substitution for this date and period.'],
            ]);
        }
    }
}
