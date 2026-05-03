<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStepInstance;
use App\Repositories\Contracts\Workflows\WorkflowInstanceRepositoryInterface;
use App\Support\Pagination\PaginationDefaults;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WorkflowInstanceRepository implements WorkflowInstanceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->listQuery()
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
            ->paginate(PaginationDefaults::resolvePerPage($perPage));
    }

    public function findOrFail(int $id): WorkflowInstance
    {
        return $this->detailQuery()->findOrFail($id);
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

    protected function baseQuery(): Builder
    {
        return WorkflowInstance::query()->select([
            'workflow_instances.id',
            'workflow_instances.school_id',
            'workflow_instances.workflow_definition_id',
            'workflow_instances.reference_type',
            'workflow_instances.reference_id',
            'workflow_instances.current_step_id',
            'workflow_instances.status',
            'workflow_instances.started_by',
            'workflow_instances.started_at',
            'workflow_instances.completed_at',
            'workflow_instances.metadata',
            'workflow_instances.created_at',
            'workflow_instances.updated_at',
            'workflow_instances.deleted_at',
        ]);
    }

    protected function listQuery(): Builder
    {
        return $this->baseQuery()->with([
            'workflowDefinition:id,name,code,module,trigger_type,status',
            'currentStep:id,workflow_definition_id,step_name,step_type,sequence,status',
            'starter:id,name,email',
        ]);
    }

    protected function detailQuery(): Builder
    {
        return $this->baseQuery()->with([
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
