<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\WorkflowDefinition;
use App\Repositories\Contracts\Workflows\WorkflowDefinitionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkflowDefinitionRepository implements WorkflowDefinitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $workflowQuery) use ($search): void {
                    $workflowQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
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

    public function findOrFail(int $id): WorkflowDefinition
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): WorkflowDefinition
    {
        $workflowDefinition = WorkflowDefinition::create($attributes);

        return $this->findOrFail($workflowDefinition->id);
    }

    public function update(WorkflowDefinition $workflowDefinition, array $attributes): WorkflowDefinition
    {
        $workflowDefinition->update($attributes);

        return $this->findOrFail($workflowDefinition->id);
    }

    public function delete(WorkflowDefinition $workflowDefinition): void
    {
        $workflowDefinition->delete();
    }

    protected function query(): Builder
    {
        return WorkflowDefinition::query()
            ->with(['creator', 'steps.assignedRole', 'steps.assignedUser'])
            ->withCount(['steps', 'instances']);
    }
}
