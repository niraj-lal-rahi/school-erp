<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetableVersionData;
use App\Models\Timetable\TimetableVersion;
use Illuminate\Database\Eloquent\Collection;

interface TimetableVersionRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetableVersionData $data): TimetableVersion;

    public function update(TimetableVersion $version, TimetableVersionData $data): TimetableVersion;

    public function delete(TimetableVersion $version): void;

    public function archiveOtherPublished(int $schoolId, int $academicYearId, ?int $ignoreId = null): void;
}
