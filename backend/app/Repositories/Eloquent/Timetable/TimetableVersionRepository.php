<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetableVersionData;
use App\Models\Timetable\TimetableVersion;
use App\Repositories\Contracts\Timetable\TimetableVersionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetableVersionRepository implements TimetableVersionRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return TimetableVersion::query()
            ->with(['academicYear', 'creator', 'publishLogs'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($versionQuery) use ($search): void {
                    $versionQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn ($query, int|string $academicYearId) => $query->where('academic_year_id', $academicYearId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(TimetableVersionData $data): TimetableVersion
    {
        /** @var TimetableVersion $version */
        $version = TimetableVersion::create($data->attributes);

        return $version->load(['academicYear', 'creator', 'publishLogs']);
    }

    public function update(TimetableVersion $version, TimetableVersionData $data): TimetableVersion
    {
        $version->update($data->attributes);

        return $version->refresh()->load(['academicYear', 'creator', 'publishLogs']);
    }

    public function delete(TimetableVersion $version): void
    {
        $version->delete();
    }

    public function archiveOtherPublished(int $schoolId, int $academicYearId, ?int $ignoreId = null): void
    {
        TimetableVersion::query()
            ->where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->where('status', 'published')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->update([
                'status' => 'archived',
            ]);
    }
}
