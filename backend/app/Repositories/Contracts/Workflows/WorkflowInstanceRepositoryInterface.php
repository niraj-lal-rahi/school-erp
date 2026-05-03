<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\WorkflowInstance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WorkflowInstanceRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): WorkflowInstance;

    public function create(array $attributes): WorkflowInstance;

    public function update(WorkflowInstance $workflowInstance, array $attributes): WorkflowInstance;

    public function delete(WorkflowInstance $workflowInstance): void;

    public function createStepInstance(array $attributes);

    public function listPendingApprovals(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByReference(string $referenceType, int $referenceId): Collection;
}
