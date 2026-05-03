<?php

namespace App\Repositories\Eloquent\Workflows;

use App\Models\Workflows\WorkflowStep;
use App\Repositories\Contracts\Workflows\WorkflowStepRepositoryInterface;
use Illuminate\Support\Collection;

class WorkflowStepRepository implements WorkflowStepRepositoryInterface
{
    public function listByDefinition(int $workflowDefinitionId): Collection
    {
        return $this->query()
            ->where('workflow_definition_id', $workflowDefinitionId)
            ->orderBy('sequence')
            ->get();
    }

    public function findOrFail(int $id): WorkflowStep
    {
        return $this->query()->findOrFail($id);
    }

    public function create(array $attributes): WorkflowStep
    {
        $workflowStep = WorkflowStep::create($attributes);

        return $this->findOrFail($workflowStep->id);
    }

    public function update(WorkflowStep $workflowStep, array $attributes): WorkflowStep
    {
        $workflowStep->update($attributes);

        return $this->findOrFail($workflowStep->id);
    }

    public function delete(WorkflowStep $workflowStep): void
    {
        $workflowStep->delete();
    }

    public function reorderForDefinition(int $workflowDefinitionId, array $orderedStepIds): void
    {
        foreach (array_values($orderedStepIds) as $index => $stepId) {
            WorkflowStep::query()
                ->where('workflow_definition_id', $workflowDefinitionId)
                ->whereKey($stepId)
                ->update(['sequence' => $index + 1]);
        }
    }

    protected function query()
    {
        return WorkflowStep::query()->with([
            'workflowDefinition',
            'assignedRole',
            'assignedUser',
        ]);
    }
}
