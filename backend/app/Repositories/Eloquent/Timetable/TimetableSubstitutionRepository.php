<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetableSubstitutionData;
use App\Models\Timetable\TimetableSubstitution;
use App\Repositories\Contracts\Timetable\TimetableSubstitutionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetableSubstitutionRepository implements TimetableSubstitutionRepositoryInterface
{
    protected array $with = [
        'timetableEntry.timetableVersion',
        'timetableEntry.schoolClass',
        'timetableEntry.section',
        'timetableEntry.period',
        'timetableEntry.subject',
        'timetableEntry.room',
        'originalStaff',
        'substituteStaff',
        'approver',
    ];

    public function all(array $filters = []): Collection
    {
        return TimetableSubstitution::query()
            ->with($this->with)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('originalStaff', fn ($staffQuery) => $staffQuery->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('substituteStaff', fn ($staffQuery) => $staffQuery->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('timetableEntry.subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('timetableEntry.room', fn ($roomQuery) => $roomQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('timetableEntry.schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->whereHas('timetableEntry', fn ($entryQuery) => $entryQuery->where('academic_year_id', $value)))
            ->when($filters['school_class_id'] ?? null, fn ($query, $value) => $query->whereHas('timetableEntry', fn ($entryQuery) => $entryQuery->where('school_class_id', $value)))
            ->when($filters['section_id'] ?? null, fn ($query, $value) => $query->whereHas('timetableEntry', fn ($entryQuery) => $entryQuery->where('section_id', $value)))
            ->when($filters['staff_id'] ?? null, fn ($query, $value) => $query->where(function ($subQuery) use ($value): void {
                $subQuery->where('original_staff_id', $value)->orWhere('substitute_staff_id', $value);
            }))
            ->when($filters['room_id'] ?? null, fn ($query, $value) => $query->whereHas('timetableEntry', fn ($entryQuery) => $entryQuery->where('room_id', $value)))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->when($filters['substitution_date'] ?? null, fn ($query, $value) => $query->whereDate('substitution_date', $value))
            ->orderByDesc('substitution_date')
            ->orderByDesc('id')
            ->get();
    }

    public function create(TimetableSubstitutionData $data): TimetableSubstitution
    {
        /** @var TimetableSubstitution $substitution */
        $substitution = TimetableSubstitution::create($data->attributes);

        return $substitution->load($this->with);
    }

    public function update(TimetableSubstitution $substitution, TimetableSubstitutionData $data): TimetableSubstitution
    {
        $substitution->update($data->attributes);

        return $substitution->refresh()->load($this->with);
    }

    public function delete(TimetableSubstitution $substitution): void
    {
        $substitution->delete();
    }

    public function findSubstituteConflict(
        int $schoolId,
        int $substituteStaffId,
        string $dayOfWeek,
        int $periodId,
        string $substitutionDate,
        ?int $ignoreSubstitutionId = null,
    ): ?TimetableSubstitution {
        return TimetableSubstitution::query()
            ->with($this->with)
            ->where('school_id', $schoolId)
            ->where('substitute_staff_id', $substituteStaffId)
            ->whereDate('substitution_date', $substitutionDate)
            ->whereIn('status', ['planned', 'approved'])
            ->whereHas('timetableEntry', function ($query) use ($dayOfWeek, $periodId): void {
                $query->where('day_of_week', $dayOfWeek)
                    ->where('attendance_period_id', $periodId);
            })
            ->when($ignoreSubstitutionId, fn ($query) => $query->where('id', '!=', $ignoreSubstitutionId))
            ->first();
    }
}
