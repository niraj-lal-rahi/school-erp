<?php

namespace App\Repositories\Contracts\Reports;

use App\Models\Reports\ReportSchedule;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReportScheduleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ReportSchedule;

    public function create(array $attributes): ReportSchedule;

    public function update(ReportSchedule $reportSchedule, array $attributes): ReportSchedule;

    public function delete(ReportSchedule $reportSchedule): void;

    public function dueSchedules(?string $at = null): Collection;
}
