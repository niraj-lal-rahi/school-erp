<?php

namespace App\Repositories\Eloquent\Timetable;

use App\DataTransferObjects\Timetable\TimetablePublishLogData;
use App\Models\Timetable\TimetablePublishLog;
use App\Repositories\Contracts\Timetable\TimetablePublishLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetablePublishLogRepository implements TimetablePublishLogRepositoryInterface
{
    public function all(array $filters = []): Collection
    {
        return TimetablePublishLog::query()
            ->with(['timetableVersion', 'performer'])
            ->when($filters['timetable_version_id'] ?? null, fn ($query, int|string $versionId) => $query->where('timetable_version_id', $versionId))
            ->when($filters['action'] ?? null, fn ($query, string $action) => $query->where('action', $action))
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(TimetablePublishLogData $data): TimetablePublishLog
    {
        /** @var TimetablePublishLog $log */
        $log = TimetablePublishLog::create($data->attributes);

        return $log->load(['timetableVersion', 'performer']);
    }
}
