<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetableEntryData;
use App\Models\Timetable\TimetableEntry;
use App\Repositories\Contracts\Timetable\TimetableEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableEntryService
{
    public function __construct(
        protected TimetableEntryRepositoryInterface $entries,
        protected TimetableConflictService $conflicts,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->entries->all($filters);
    }

    public function create(TimetableEntryData $data): TimetableEntry
    {
        return DB::transaction(function () use ($data): TimetableEntry {
            $conflicts = $this->conflicts->validateEntry($data->attributes);
            $this->throwIfConflicts($conflicts);

            return $this->entries->create($data);
        });
    }

    public function update(TimetableEntry $entry, TimetableEntryData $data): TimetableEntry
    {
        return DB::transaction(function () use ($entry, $data): TimetableEntry {
            $conflicts = $this->conflicts->validateEntry($data->attributes, $entry->id);
            $this->throwIfConflicts($conflicts);

            return $this->entries->update($entry, $data);
        });
    }

    public function delete(TimetableEntry $entry): void
    {
        DB::transaction(function () use ($entry): void {
            if ($entry->timetableVersion->status !== 'draft') {
                throw ValidationException::withMessages([
                    'timetable_version_id' => ['Only draft timetable versions can be edited.'],
                ]);
            }

            $this->entries->delete($entry);
        });
    }

    public function bulkCreate(array $payloads): Collection
    {
        return DB::transaction(function () use ($payloads): Collection {
            $entryData = [];

            foreach ($payloads as $payload) {
                $conflicts = $this->conflicts->validateEntry($payload);
                $this->throwIfConflicts($conflicts);
                $entryData[] = TimetableEntryData::fromArray($payload);
            }

            return $this->entries->bulkCreate($entryData);
        });
    }

    public function checkConflicts(array $attributes, ?int $ignoreEntryId = null): array
    {
        return $this->conflicts->validateEntry($attributes, $ignoreEntryId);
    }

    public function weeklyForClassSection(int $schoolId, int $classId, int $sectionId, array $filters = []): Collection
    {
        return $this->entries->weeklyForClassSection($schoolId, $classId, $sectionId, $filters);
    }

    public function weeklyForStaff(int $schoolId, int $staffId, array $filters = []): Collection
    {
        return $this->entries->weeklyForStaff($schoolId, $staffId, $filters);
    }

    public function dailyForStaff(int $schoolId, int $staffId, string $dayOfWeek, array $filters = []): Collection
    {
        return $this->entries->dailyForStaff($schoolId, $staffId, $dayOfWeek, $filters);
    }

    protected function throwIfConflicts(array $conflicts): void
    {
        if ($conflicts === []) {
            return;
        }

        throw ValidationException::withMessages([
            'conflicts' => collect($conflicts)->pluck('message')->all(),
        ]);
    }
}
