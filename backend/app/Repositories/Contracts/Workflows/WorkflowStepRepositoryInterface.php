<?php

namespace App\Repositories\Contracts\Workflows;

use App\Models\Workflows\WorkflowStep;
use Illuminate\Support\Collection;

interface WorkflowStepRepositoryInterface
{
    public function listByDefinition(int $workflowDefinitionId): Collection;

    public function findOrFail(int $id): WorkflowStep;

    public function create(array $attributes): WorkflowStep;

    public function update(WorkflowStep $workflowStep, array $attributes): WorkflowStep;

    public function delete(WorkflowStep $workflowStep): void;

    public function reorderForDefinition(int $workflowDefinitionId, array $orderedStepIds): void;
}
