<?php

namespace App\Services\Workflows;

use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowStep;
use App\Repositories\Contracts\Workflows\WorkflowDefinitionRepositoryInterface;
use App\Repositories\Contracts\Workflows\WorkflowStepRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkflowDefinitionService
{
    public function __construct(
        protected WorkflowDefinitionRepositoryInterface $definitions,
        protected WorkflowStepRepositoryInterface $steps,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->definitions->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): WorkflowDefinition
    {
        return $this->definitions->findOrFail($id);
    }

    public function findStepOrFail(int $id): WorkflowStep
    {
        return $this->steps->findOrFail($id);
    }

    public function listSteps(WorkflowDefinition $workflowDefinition): Collection
    {
        return $this->steps->listByDefinition($workflowDefinition->id);
    }

    public function create(array $attributes): WorkflowDefinition
    {
        return DB::transaction(fn (): WorkflowDefinition => $this->definitions->create($attributes));
    }

    public function update(WorkflowDefinition $workflowDefinition, array $attributes): WorkflowDefinition
    {
        return DB::transaction(fn (): WorkflowDefinition => $this->definitions->update($workflowDefinition, $attributes));
    }

    public function delete(WorkflowDefinition $workflowDefinition): void
    {
        DB::transaction(function () use ($workflowDefinition): void {
            $this->definitions->delete($workflowDefinition);
        });
    }

    public function activate(WorkflowDefinition $workflowDefinition): WorkflowDefinition
    {
        return DB::transaction(fn (): WorkflowDefinition => $this->definitions->update($workflowDefinition, [
            'status' => 'active',
        ]));
    }

    public function deactivate(WorkflowDefinition $workflowDefinition): WorkflowDefinition
    {
        return DB::transaction(fn (): WorkflowDefinition => $this->definitions->update($workflowDefinition, [
            'status' => 'inactive',
        ]));
    }

    public function addStep(WorkflowDefinition $workflowDefinition, array $attributes): WorkflowStep
    {
        return DB::transaction(function () use ($workflowDefinition, $attributes): WorkflowStep {
            $sequence = (int) ($attributes['sequence'] ?? ($workflowDefinition->steps()->count() + 1));

            return $this->steps->create([
                'school_id' => $workflowDefinition->school_id,
                'workflow_definition_id' => $workflowDefinition->id,
                'step_name' => $attributes['step_name'],
                'step_type' => $attributes['step_type'],
                'sequence' => $sequence,
                'config' => $attributes['config'] ?? null,
                'assigned_role_id' => $attributes['assigned_role_id'] ?? null,
                'assigned_user_id' => $attributes['assigned_user_id'] ?? null,
                'status' => $attributes['status'] ?? 'active',
            ]);
        });
    }

    public function updateStep(WorkflowStep $workflowStep, array $attributes): WorkflowStep
    {
        return DB::transaction(fn (): WorkflowStep => $this->steps->update($workflowStep, $attributes));
    }

    public function deleteStep(WorkflowStep $workflowStep): void
    {
        DB::transaction(function () use ($workflowStep): void {
            $definitionId = $workflowStep->workflow_definition_id;
            $this->steps->delete($workflowStep);

            $remainingIds = $this->steps->listByDefinition($definitionId)->pluck('id')->all();
            $this->steps->reorderForDefinition($definitionId, $remainingIds);
        });
    }

    public function reorderSteps(WorkflowDefinition $workflowDefinition, array $orderedStepIds): Collection
    {
        return DB::transaction(function () use ($workflowDefinition, $orderedStepIds): Collection {
            $this->steps->reorderForDefinition($workflowDefinition->id, $orderedStepIds);

            return $this->steps->listByDefinition($workflowDefinition->id);
        });
    }
}
