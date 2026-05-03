<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\AutomationRule;
use App\Repositories\Contracts\Workflows\AutomationRuleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AutomationRuleRepository implements AutomationRuleRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $automationQuery) use ($search): void {
                    $automationQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['trigger_type'] ?? null, fn (Builder $query, string $value) => $query->where('trigger_type', $value))
            ->when($filters['trigger_event'] ?? null, fn (Builder $query, string $value) => $query->where('trigger_event', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function allActiveByTrigger(string $triggerType, ?string $triggerEvent = null): Collection
    {
        return $this->query()
            ->where('status', 'active')
            ->where('trigger_type', $triggerType)
            ->when($triggerEvent !== null, fn (Builder $query) => $query->where('trigger_event', $triggerEvent))
            ->orderBy('id')
            ->get();
    }

    public function dueScheduledRules(): Collection
    {
        return $this->query()
            ->where('status', 'active')
            ->where('trigger_type', 'schedule')
            ->whereNotNull('schedule_expression')
            ->where(function (Builder $query): void {
                $query->whereNull('next_run_at')
                    ->orWhere('next_run_at', '<=', now());
            })
            ->orderBy('next_run_at')
            ->get();
    }

    public function findOrFail(int $id): AutomationRule
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): AutomationRule
    {
        $automationRule = AutomationRule::create($attributes);

        return $this->findOrFail($automationRule->id);
    }

    public function update(AutomationRule $automationRule, array $attributes): AutomationRule
    {
        $automationRule->update($attributes);

        return $this->findOrFail($automationRule->id);
    }

    public function delete(AutomationRule $automationRule): void
    {
        $automationRule->delete();
    }

    protected function query(): Builder
    {
        return AutomationRule::query()
            ->with(['creator'])
            ->withCount(['runs', 'actionLogs']);
    }
}
