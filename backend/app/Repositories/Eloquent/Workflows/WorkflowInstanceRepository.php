<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStepInstance;
use App\Repositories\Contracts\Workflows\WorkflowInstanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkflowInstanceRepository implements WorkflowInstanceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['module'] ?? null, function (Builder $query, string $value): void {
                $query->whereHas('workflowDefinition', fn (Builder $definitionQuery) => $definitionQuery->where('module', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['reference_type'] ?? null, fn (Builder $query, string $value) => $query->where('reference_type', $value))
            ->when($filters['reference_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('reference_id', $value))
            ->when($filters['workflow_definition_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('workflow_definition_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('started_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('created_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): WorkflowInstance
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): WorkflowInstance
    {
        $workflowInstance = WorkflowInstance::create($attributes);

        return $this->findOrFail($workflowInstance->id);
    }

    public function update(WorkflowInstance $workflowInstance, array $attributes): WorkflowInstance
    {
        $workflowInstance->update($attributes);

        return $this->findOrFail($workflowInstance->id);
    }

    public function delete(WorkflowInstance $workflowInstance): void
    {
        $workflowInstance->delete();
    }

    public function createStepInstance(array $attributes): WorkflowStepInstance
    {
        return WorkflowStepInstance::create($attributes)->load([
            'workflowStep.assignedRole',
            'workflowStep.assignedUser',
            'assignee',
            'actor',
        ]);
    }

    public function listPendingApprovals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return ApprovalRequest::query()
            ->with(['workflowInstance.workflowDefinition', 'requester', 'approver', 'approverRole'])
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? 'pending', fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['reference_type'] ?? null, fn (Builder $query, string $value) => $query->where('reference_type', $value))
            ->when($filters['reference_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('reference_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('requested_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('requested_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findByReference(string $referenceType, int $referenceId): Collection
    {
        return $this->query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->latest('id')
            ->get();
    }

    protected function query(): Builder
    {
        return WorkflowInstance::query()->with([
            'workflowDefinition',
            'currentStep',
            'starter',
            'stepInstances.workflowStep.assignedRole',
            'stepInstances.workflowStep.assignedUser',
            'stepInstances.assignee',
            'stepInstances.actor',
            'approvalRequests.approver',
            'approvalRequests.approverRole',
        ]);
    }
}
