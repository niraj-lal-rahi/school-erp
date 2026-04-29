<?php

namespace App\Repositories\Contracts\Reports;

use App\Models\Reports\ReportRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReportRunRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): ReportRun;

    public function create(array $attributes): ReportRun;

    public function update(ReportRun $reportRun, array $attributes): ReportRun;

    public function delete(ReportRun $reportRun): void;
}
