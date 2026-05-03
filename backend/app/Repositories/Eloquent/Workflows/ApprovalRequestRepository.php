<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\ApprovalRequest;
use App\Repositories\Contracts\Workflows\ApprovalRequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ApprovalRequestRepository implements ApprovalRequestRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['module'] ?? null, fn (Builder $query, string $value) => $query->where('module', $value))
            ->when($filters['status'] ?? null, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($filters['reference_type'] ?? null, fn (Builder $query, string $value) => $query->where('reference_type', $value))
            ->when($filters['reference_id'] ?? null, fn (Builder $query, int|string $value) => $query->where('reference_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $value) => $query->whereDate('requested_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $value) => $query->whereDate('requested_at', '<=', $value))
            ->latest('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ApprovalRequest
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): ApprovalRequest
    {
        $approvalRequest = ApprovalRequest::create($attributes);

        return $this->findOrFail($approvalRequest->id);
    }

    public function update(ApprovalRequest $approvalRequest, array $attributes): ApprovalRequest
    {
        $approvalRequest->update($attributes);

        return $this->findOrFail($approvalRequest->id);
    }

    public function delete(ApprovalRequest $approvalRequest): void
    {
        $approvalRequest->delete();
    }

    protected function query(): Builder
    {
        return ApprovalRequest::query()->with([
            'workflowInstance.workflowDefinition',
            'requester',
            'approver',
            'approverRole',
        ]);
    }
}
