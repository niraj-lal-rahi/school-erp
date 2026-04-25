<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetableEntryData;
use App\Models\Timetable\TimetableEntry;
use Illuminate\Database\Eloquent\Collection;

interface TimetableEntryRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetableEntryData $data): TimetableEntry;

    public function update(TimetableEntry $entry, TimetableEntryData $data): TimetableEntry;

    public function delete(TimetableEntry $entry): void;

    public function bulkCreate(array $entries): Collection;

    public function findBySlot(
        int $schoolId,
        int $versionId,
        int $classId,
        int $sectionId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry;

    public function findTeacherConflict(
        int $schoolId,
        int $versionId,
        int $staffId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry;

    public function findRoomConflict(
        int $schoolId,
        int $versionId,
        int $roomId,
        string $dayOfWeek,
        int $periodId,
        ?int $ignoreId = null,
    ): ?TimetableEntry;

    public function weeklyForClassSection(int $schoolId, int $classId, int $sectionId, array $filters = []): Collection;

    public function weeklyForStaff(int $schoolId, int $staffId, array $filters = []): Collection;

    public function dailyForStaff(int $schoolId, int $staffId, string $dayOfWeek, array $filters = []): Collection;
}
