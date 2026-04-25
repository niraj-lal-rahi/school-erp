<?php

namespace App\Services\Timetable;

use App\DataTransferObjects\Timetable\TimetablePublishLogData;
use App\Models\Timetable\TimetablePublishLog;
use App\Repositories\Contracts\Timetable\TimetablePublishLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TimetablePublishLogService
{
    public function __construct(
        protected TimetablePublishLogRepositoryInterface $logs,
    ) {
    }

    public function all(array $filters = []): Collection
    {
        return $this->logs->all($filters);
    }

    public function create(array $payload): TimetablePublishLog
    {
        return $this->logs->create(TimetablePublishLogData::fromArray($payload));
    }
}
