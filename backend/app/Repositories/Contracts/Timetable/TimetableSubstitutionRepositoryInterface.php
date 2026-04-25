<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetableSubstitutionData;
use App\Models\Timetable\TimetableSubstitution;
use Illuminate\Database\Eloquent\Collection;

interface TimetableSubstitutionRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetableSubstitutionData $data): TimetableSubstitution;

    public function update(TimetableSubstitution $substitution, TimetableSubstitutionData $data): TimetableSubstitution;

    public function delete(TimetableSubstitution $substitution): void;

    public function findSubstituteConflict(
        int $schoolId,
        int $substituteStaffId,
        string $dayOfWeek,
        int $periodId,
        string $substitutionDate,
        ?int $ignoreSubstitutionId = null,
    ): ?TimetableSubstitution;
}
