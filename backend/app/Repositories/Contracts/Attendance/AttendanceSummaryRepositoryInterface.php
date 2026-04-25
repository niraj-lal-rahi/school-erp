<?php

namespace App\Repositories\Contracts\Attendance;

use App\DataTransferObjects\Attendance\AttendanceSummaryData;
use App\Models\Attendance\AttendanceSummary;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceSummaryRepositoryInterface
{
    public function all(array $filters = []): Collection;

    public function upsert(AttendanceSummaryData $data): AttendanceSummary;
}
