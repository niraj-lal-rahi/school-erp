<?php

namespace App\Services\Workflows;

use App\Events\Workflows\WorkflowCompleted;
use App\Events\Workflows\WorkflowStarted;
use App\Models\User;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowInstance;
use App\Models\Workflows\WorkflowStep;
use App\Models\Workflows\WorkflowStepInstance;
use App\Repositories\Contracts\Workflows\WorkflowDefinitionRepositoryInterface;
use App\Repositories\Contracts\Workflows\WorkflowInstanceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkflowRuntimeService
{
    public function __construct(
        protected WorkflowDefinitionRepositoryInterface $definitions,
        protected WorkflowInstanceRepositoryInterface $instances,
        protected ApprovalService $approvals,
        protected ActionExecutorService $actions,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->instances->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): WorkflowInstance
    {
        return $this->instances->findOrFail($id);
    }

    public function summary(array $filters = []): array
    {
        $query = WorkflowInstance::withoutGlobalScopes()
            ->when($filters['school_id'] ?? null, fn (Builder $builder, $value) => $builder->where('school_id', $value))
            ->when($filters['module'] ?? null, function (Builder $builder, string $value): void {
                $builder->whereHas('workflowDefinition', fn (Builder $definitionQuery) => $definitionQuery->where('module', $value));
            })
            ->when($filters['status'] ?? null, fn (Builder $builder, string $value) => $builder->where('status', $value))
            ->when($filters['reference_type'] ?? null, fn (Builder $builder, string $value) => $builder->where('reference_type', $value))
            ->when($filters['reference_id'] ?? null, fn (Builder $builder, $value) => $builder->where('reference_id', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '<=', $value));

        return [
            'total_instances' => (clone $query)->count(),
            'pending_instances' => (clone $query)->where('status', 'pending')->count(),
            'in_progress_instances' => (clone $query)->where('status', 'in_progress')->count(),
            'approved_instances' => (clone $query)->where('status', 'approved')->count(),
            'rejected_instances' => (clone $query)->where('status', 'rejected')->count(),
            'completed_instances' => (clone $query)->where('status', 'completed')->count(),
            'failed_instances' => (clone $query)->where('status', 'failed')->count(),
            'by_module' => WorkflowDefinition::withoutGlobalScopes()
                ->selectRaw('module, COUNT(workflow_instances.id) as instances_count')
                ->join('workflow_instances', 'workflow_instances.workflow_definition_id', '=', 'workflow_definitions.id')
                ->when($filters['school_id'] ?? null, fn (Builder $builder, $value) => $builder->where('workflow_instances.school_id', $value))
                ->groupBy('module')
                ->get(),
        ];
    }

    public function start(array $attributes, ?User $startedBy = null): WorkflowInstance
    {
        $definition = $this->definitions->findOrFail((int) $attributes['workflow_definition_id']);

        if ($definition->status !== 'active') {
            throw ValidationException::withMessages([
                'workflow_definition_id' => ['Only active workflows can be started.'],
            ]);
        }

        return DB::transaction(function () use ($definition, $attributes, $startedBy): WorkflowInstance {
            $definition->loadMissing('steps');

            if ($definition->steps->isEmpty()) {
                throw ValidationException::withMessages([
                    'workflow_definition_id' => ['The selected workflow has no steps configured.'],
                ]);
            }

            $instance = $this->instances->create([
                'school_id' => $definition->school_id,
                'workflow_definition_id' => $definition->id,
                'reference_type' => $attributes['reference_type'],
                'reference_id' => (int) $attributes['reference_id'],
                'current_step_id' => null,
                'status' => 'pending',
                'started_by' => $startedBy?->id ?? ($attributes['started_by'] ?? null),
                'started_at' => now(),
                'completed_at' => null,
                'metadata' => $attributes['metadata'] ?? [],
            ]);

            foreach ($definition->steps as $step) {
                $this->instances->createStepInstance([
                    'school_id' => $definition->school_id,
                    'workflow_instance_id' => $instance->id,
                    'workflow_step_id' => $step->id,
                    'assigned_to' => $step->assigned_user_id,
                    'status' => 'pending',
                    'metadata' => [
                        'step_type' => $step->step_type,
                        'sequence' => $step->sequence,
                    ],
                ]);
            }

            $instance = $this->instances->update($instance, [
                'status' => 'in_progress',
            ]);

            DB::afterCommit(fn () => event(new WorkflowStarted($instance)));

            return $this->advance($instance);
        });
    }

    public function advance(WorkflowInstance $workflowInstance): WorkflowInstance
    {
        return DB::transaction(function () use ($workflowInstance): WorkflowInstance {
            $instance = $this->instances->findOrFail($workflowInstance->id);
            $stepInstances = $instance->stepInstances->sortBy(fn (WorkflowStepInstance $stepInstance) => $stepInstance->workflowStep?->sequence ?? PHP_INT_MAX)->values();

            $current = $stepInstances->first(fn (WorkflowStepInstance $stepInstance) => $stepInstance->status === 'pending');

            if (! $current) {
                return $this->complete($instance);
            }

            $step = $current->workflowStep;
            if (! $step) {
                return $this->fail($instance, 'The current workflow step could not be resolved.');
            }

            $this->instances->update($instance, [
                'current_step_id' => $step->id,
                'status' => 'in_progress',
            ]);

            if ($step->step_type === 'approval') {
                $this->approvals->ensureApprovalRequest($instance, $current, $step);

                return $this->instances->findOrFail($instance->id);
            }

            if ($step->step_type === 'delay') {
                return $this->instances->findOrFail($instance->id);
            }

            $this->processAutomaticStep($instance, $current, $step);

            return $this->advance($instance);
        });
    }

    public function cancel(WorkflowInstance $workflowInstance, ?User $cancelledBy = null, ?string $remarks = null): WorkflowInstance
    {
        return DB::transaction(function () use ($workflowInstance, $cancelledBy, $remarks): WorkflowInstance {
            foreach ($workflowInstance->stepInstances()->where('status', 'pending')->get() as $stepInstance) {
                $stepInstance->update([
                    'status' => 'skipped',
                    'action_taken_by' => $cancelledBy?->id,
                    'action_taken_at' => now(),
                    'remarks' => $remarks ?: 'Workflow cancelled.',
                    'metadata' => array_merge($stepInstance->metadata ?? [], ['cancelled' => true]),
                ]);
            }

            return $this->instances->update($workflowInstance, [
                'status' => 'cancelled',
                'completed_at' => now(),
                'metadata' => array_merge($workflowInstance->metadata ?? [], [
                    'cancelled_by' => $cancelledBy?->id,
                    'cancelled_remarks' => $remarks,
                ]),
            ]);
        });
    }

    public function complete(WorkflowInstance $workflowInstance): WorkflowInstance
    {
        $workflowInstance = $this->instances->update($workflowInstance, [
            'current_step_id' => null,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        DB::afterCommit(fn () => event(new WorkflowCompleted($workflowInstance)));

        return $workflowInstance;
    }

    public function fail(WorkflowInstance $workflowInstance, string $message): WorkflowInstance
    {
        return $this->instances->update($workflowInstance, [
            'status' => 'failed',
            'completed_at' => now(),
            'metadata' => array_merge($workflowInstance->metadata ?? [], [
                'failure_reason' => $message,
            ]),
        ]);
    }

    protected function processAutomaticStep(WorkflowInstance $instance, WorkflowStepInstance $stepInstance, WorkflowStep $step): void
    {
        if ($step->step_type === 'condition') {
            $matched = $this->actions->evaluateConditions($step->config['conditions'] ?? null, $this->buildExecutionContext($instance, $stepInstance, $step));

            $stepInstance->update([
                'status' => $matched ? 'completed' : 'skipped',
                'action_taken_at' => now(),
                'remarks' => $matched ? 'Condition matched.' : 'Condition not matched.',
                'metadata' => array_merge($stepInstance->metadata ?? [], ['condition_matched' => $matched]),
            ]);

            return;
        }

        $response = $this->actions->executeStep($step, $this->buildExecutionContext($instance, $stepInstance, $step));

        $stepInstance->update([
            'status' => 'completed',
            'action_taken_at' => now(),
            'remarks' => 'Automatic step executed.',
            'metadata' => array_merge($stepInstance->metadata ?? [], ['execution' => $response]),
        ]);
    }

    protected function buildExecutionContext(WorkflowInstance $instance, WorkflowStepInstance $stepInstance, WorkflowStep $step): array
    {
        return [
            'school_id' => $instance->school_id,
            'workflow_instance' => $instance->fresh(['workflowDefinition', 'currentStep', 'starter', 'approvalRequests']),
            'workflow_step_instance' => $stepInstance->fresh(['workflowStep', 'assignee', 'actor']),
            'workflow_step' => $step,
            'reference' => $this->resolveReferenceRecord($instance),
            'metadata' => $instance->metadata ?? [],
        ];
    }

    protected function resolveReferenceRecord(WorkflowInstance $instance): mixed
    {
        $class = $instance->reference_type;

        if (! class_exists($class) || ! method_exists($class, 'query')) {
            return null;
        }

        return $class::query()->find($instance->reference_id);
    }
}
