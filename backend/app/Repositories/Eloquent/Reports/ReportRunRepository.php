<?php

namespace App\Repositories\Eloquent\Reports;

use App\Models\Reports\ReportRun;
use App\Repositories\Contracts\Reports\ReportRunRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReportRunRepository implements ReportRunRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['module'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('reportDefinition', fn (Builder $definitionQuery) => $definitionQuery->where('module', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['report_definition_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('report_definition_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('started_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('completed_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ReportRun
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ReportRun
    {
        $reportRun = ReportRun::create($attributes);

        return $this->findOrFail($reportRun->id);
    }

    public function update(ReportRun $reportRun, array $attributes): ReportRun
    {
        $reportRun->update($attributes);

        return $this->findOrFail($reportRun->id);
    }

    public function delete(ReportRun $reportRun): void
    {
        $reportRun->delete();
    }

    protected function query(): Builder
    {
        return ReportRun::query()->with([
            'reportDefinition',
            'initiator',
            'exports',
        ]);
    }
}
