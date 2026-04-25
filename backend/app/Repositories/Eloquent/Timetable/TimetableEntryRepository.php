<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetableEntryData;
use App\Models\Timetable\TimetableEntry;
use App\Repositories\Contracts\Timetable\TimetableEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetableEntryRepository implements TimetableEntryRepositoryInterface
{
    protected array $with = [
        'timetableVersion',
        'academicYear',
        'schoolClass',
        'section',
        'period',
        'subject',
        'staff',
        'room',
        'substitutions.originalStaff',
        'substitutions.substituteStaff',
        'substitutions.approver',
    ];

    public function all(array $filters = []): Collection
    {
        return TimetableEntry::query()
            ->with($this->with)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($entryQuery) use ($search): void {
                    $entryQuery->where('day_of_week', 'like', "%{$search}%")
                        ->orWhere('entry_type', 'like', "%{$search}%")
                        ->orWhereHas('subject', fn ($subjectQuery) => $subjectQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('staff', fn ($staffQuery) => $staffQuery->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn ($classQuery) => $classQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($filters['school_class_id'] ?? null, fn ($query, $value) => $query->where('school_class_id', $value))
            ->when($filters['section_id'] ?? null, fn ($query, $value) => $query->where('section_id', $value))
            ->when($filters['staff_id'] ?? null, fn ($query, $value) => $query->where('staff_id', $value))
            ->when($filters['room_id'] ?? null, fn ($query, $value) => $query->where('room_id', $value))
            ->when($filters['day_of_week'] ?? null, fn ($query, $value) => $query->where('day_of_week', $value))
            ->when($filters['timetable_version_id'] ?? null, fn ($query, $value) => $query->where('timetable_version_id', $value))
            ->when($filters['status'] ?? null, fn ($query, $value) => $query->where('status', $value))
            ->orderBy('day_of_week')
            ->orderBy('attendance_period_id')
            ->get();
    }

    public function create(TimetableEntryData $data): TimetableEntry
    {
        /** @var TimetableEntry $entry */
        $entry = TimetableEntry::create($data->attributes);

        return $entry->load($this->with);
    }

    public function update(TimetableEntry $entry, TimetableEntryData $data): TimetableEntry
    {
        $entry->update($data->attributes);

        return $entry->refresh()->load($this->with);
    }

    public function delete(TimetableEntry $entry): void
    {
        $entry->delete();
    }

    public function bulkCreate(array $entries): Collection
    {
        $created = new Collection();

        foreach ($entries as $entryData) {
            $created->push($this->create($entryData));
        }

        return $created;
    }

    public function findBySlot(
        int $schoolId,
        int $versionId,
        int $classId,
        int $sectionId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry {
        return TimetableEntry::query()
            ->where('school_id', $schoolId)
            ->where('timetable_version_id', $versionId)
            ->where('school_class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('day_of_week', $dayOfWeek)
            ->where('attendance_period_id', $periodId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->first();
    }

    public function findTeacherConflict(
        int $schoolId,
        int $versionId,
        int $staffId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry {
        return TimetableEntry::query()
            ->where('school_id', $schoolId)
            ->where('timetable_version_id', $versionId)
            ->where('staff_id', $staffId)
            ->where('day_of_week', $dayOfWeek)
            ->where('attendance_period_id', $periodId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->first();
    }

    public function findRoomConflict(
        int $schoolId,
        int $versionId,
        int $roomId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry {
        return TimetableEntry::query()
            ->where('school_id', $schoolId)
            ->where('timetable_version_id', $versionId)
            ->where('room_id', $roomId)
            ->where('day_of_week', $dayOfWeek)
            ->where('attendance_period_id', $periodId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->first();
    }

    public function weeklyForClassSection(int $schoolId, int $classId, int $sectionId, array $filters = []): Collection
    {
        return TimetableEntry::query()
            ->with($this->with)
            ->where('school_id', $schoolId)
            ->where('school_class_id', $classId)
            ->where('section_id', $sectionId)
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($filters['timetable_version_id'] ?? null, fn ($query, $value) => $query->where('timetable_version_id', $value))
            ->orderBy('day_of_week')
            ->orderBy('attendance_period_id')
            ->get();
    }

    public function weeklyForStaff(int $schoolId, int $staffId, array $filters = []): Collection
    {
        return TimetableEntry::query()
            ->with($this->with)
            ->where('school_id', $schoolId)
            ->where(function ($query) use ($staffId): void {
                $query->where('staff_id', $staffId)
                    ->orWhereHas('substitutions', function ($substitutionQuery) use ($staffId): void {
                        $substitutionQuery->where('substitute_staff_id', $staffId)
                            ->whereIn('status', ['approved', 'completed']);
                    });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($filters['timetable_version_id'] ?? null, fn ($query, $value) => $query->where('timetable_version_id', $value))
            ->orderBy('day_of_week')
            ->orderBy('attendance_period_id')
            ->get();
    }

    public function dailyForStaff(int $schoolId, int $staffId, string $dayOfWeek, array $filters = []): Collection
    {
        return TimetableEntry::query()
            ->with($this->with)
            ->where('school_id', $schoolId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($staffId): void {
                $query->where('staff_id', $staffId)
                    ->orWhereHas('substitutions', function ($substitutionQuery) use ($staffId): void {
                        $substitutionQuery->where('substitute_staff_id', $staffId)
                            ->whereIn('status', ['approved', 'completed']);
                    });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, $value) => $query->where('academic_year_id', $value))
            ->when($filters['timetable_version_id'] ?? null, fn ($query, $value) => $query->where('timetable_version_id', $value))
            ->orderBy('attendance_period_id')
            ->get();
    }
}
