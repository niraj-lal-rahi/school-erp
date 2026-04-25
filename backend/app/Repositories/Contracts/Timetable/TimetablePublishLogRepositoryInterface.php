<?php

namespace App\Repositories\Contracts\Timetable;

use App\DataTransferObjects\Timetable\TimetablePublishLogData;
use App\Models\Timetable\TimetablePublishLog;
use Illuminate\Database\Eloquent\Collection;

interface TimetablePublishLogRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function create(TimetablePublishLogData $data): TimetablePublishLog;
}
