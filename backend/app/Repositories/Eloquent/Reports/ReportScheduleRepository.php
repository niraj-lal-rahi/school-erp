<?php

namespace App\Repositories\Eloquent\Reports;

use App\Models\Reports\ReportSchedule;
use App\Repositories\Contracts\Reports\ReportScheduleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportScheduleRepository implements ReportScheduleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['module'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('reportDefinition', fn (Builder $definitionQuery) => $definitionQuery->where('module', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['report_definition_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('report_definition_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('next_run_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('next_run_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ReportSchedule
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ReportSchedule
    {
        $reportSchedule = ReportSchedule::create($attributes);

        return $this->findOrFail($reportSchedule->id);
    }

    public function update(ReportSchedule $reportSchedule, array $attributes): ReportSchedule
    {
        $reportSchedule->update($attributes);

        return $this->findOrFail($reportSchedule->id);
    }

    public function delete(ReportSchedule $reportSchedule): void
    {
        $reportSchedule->delete();
    }

    public function dueSchedules(?string $at = null): Collection
    {
        $target = $at ?? now()->toDateTimeString();

        return $this->query()
            ->where('status', 'active')
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', $target)
            ->orderBy('next_run_at')
            ->get();
    }

    protected function query(): Builder
    {
        return ReportSchedule::query()->with([
            'reportDefinition',
            'creator',
        ]);
    }
}
