<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\AutomationActionLog;
use App\Models\Workflows\AutomationRun;
use App\Repositories\Contracts\Workflows\AutomationRunRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AutomationRunRepository implements AutomationRunRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['module'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('automationRule', fn (Builder $ruleQuery) => $ruleQuery->where('module', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['trigger_type'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('automationRule', fn (Builder $ruleQuery) => $ruleQuery->where('trigger_type', $value));
            })
            ->when($filters['trigger_event'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('automationRule', fn (Builder $ruleQuery) => $ruleQuery->where('trigger_event', $value));
            })
            ->when($filters['automation_rule_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('automation_rule_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('started_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): AutomationRun
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): AutomationRun
    {
        $automationRun = AutomationRun::create($attributes);

        return $this->findOrFail($automationRun->id);
    }

    public function update(AutomationRun $automationRun, array $attributes): AutomationRun
    {
        $automationRun->update($attributes);

        return $this->findOrFail($automationRun->id);
    }

    public function createActionLog(array $attributes): AutomationActionLog
    {
        return AutomationActionLog::create($attributes);
    }

    protected function query(): Builder
    {
        return AutomationRun::query()->with([
            'automationRule',
            'actionLogs',
        ]);
    }
}
