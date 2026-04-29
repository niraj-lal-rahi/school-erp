<?php

namespace App\Repositories\Eloquent\Reports;

use App\Models\Reports\ReportDefinition;
use App\Repositories\Contracts\Reports\ReportDefinitionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReportDefinitionRepository implements ReportDefinitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $definitionQuery) use ($search): void {
                    $definitionQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ReportDefinition
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ReportDefinition
    {
        $reportDefinition = ReportDefinition::create($attributes);

        return $this->findOrFail($reportDefinition->id);
    }

    public function update(ReportDefinition $reportDefinition, array $attributes): ReportDefinition
    {
        $reportDefinition->update($attributes);

        return $this->findOrFail($reportDefinition->id);
    }

    public function delete(ReportDefinition $reportDefinition): void
    {
        $reportDefinition->delete();
    }

    protected function query(): Builder
    {
        return ReportDefinition::query()
            ->with(['creator'])
            ->withCount(['schedules', 'runs']);
    }
}
