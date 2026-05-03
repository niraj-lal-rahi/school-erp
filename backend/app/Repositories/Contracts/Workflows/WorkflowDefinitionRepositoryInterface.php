<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\WorkflowDefinition;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface WorkflowDefinitionRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function allActiveByTrigger(string $triggerType, ?string $triggerEvent = null): Collection;

    public function findOrFail(int $id): WorkflowDefinition;

    public function create(array $attributes): WorkflowDefinition;

    public function update(WorkflowDefinition $workflowDefinition, array $attributes): WorkflowDefinition;

    public function delete(WorkflowDefinition $workflowDefinition): void;
}
