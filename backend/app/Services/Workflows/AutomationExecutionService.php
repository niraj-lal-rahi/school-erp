<?php

namespace App\Services\Workflows;

use App\Events\Workflows\AutomationRuleTriggered;
use App\Models\User;
use App\Models\Workflows\AutomationRule;
use App\Models\Workflows\AutomationRun;
use App\Repositories\Contracts\Workflows\AutomationRuleRepositoryInterface;
use App\Repositories\Contracts\Workflows\AutomationRunRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AutomationExecutionService
{
    public function __construct(
        protected AutomationRuleRepositoryInterface $rules,
        protected AutomationRunRepositoryInterface $runs,
        protected ActionExecutorService $actions,
    ) {
    }

    public function paginateRuns(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->runs->paginate($filters, $perPage);
    }

    public function findRunOrFail(int $id): AutomationRun
    {
        return $this->runs->findOrFail($id);
    }

    public function summary(array $filters = []): array
    {
        $query = AutomationRun::withoutGlobalScopes()
            ->when($filters['school_id'] ?? null, fn (Builder $builder, $value) => $builder->where('school_id', $value))
            ->when($filters['status'] ?? null, fn (Builder $builder, string $value) => $builder->where('status', $value))
            ->when($filters['date_from'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $builder, string $value) => $builder->whereDate('created_at', '<=', $value))
            ->when($filters['module'] ?? null, function (Builder $builder, string $value): void {
                $builder->whereHas('automationRule', fn (Builder $ruleQuery) => $ruleQuery->where('module', $value));
            })
            ->when($filters['trigger_type'] ?? null, function (Builder $builder, string $value): void {
                $builder->whereHas('automationRule', fn (Builder $ruleQuery) => $ruleQuery->where('trigger_type', $value));
            });

        return [
            'total_runs' => (clone $query)->count(),
            'processing_runs' => (clone $query)->where('status', 'processing')->count(),
            'completed_runs' => (clone $query)->where('status', 'completed')->count(),
            'failed_runs' => (clone $query)->where('status', 'failed')->count(),
            'records_processed' => (int) (clone $query)->sum('records_processed'),
            'success_count' => (int) (clone $query)->sum('success_count'),
            'failed_count' => (int) (clone $query)->sum('failed_count'),
        ];
    }

    public function execute(AutomationRule|int $rule, array $context = [], ?User $triggeredBy = null): AutomationRun
    {
        $automationRule = $rule instanceof AutomationRule ? $rule : $this->rules->findOrFail((int) $rule);

        if ($automationRule->status !== 'active') {
            throw ValidationException::withMessages([
                'automation_rule_id' => ['Only active automation rules can be executed.'],
            ]);
        }

        $idempotencyKey = $context['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existing = AutomationRun::query()
                ->where('school_id', $automationRule->school_id)
                ->where('automation_rule_id', $automationRule->id)
                ->where('status', 'completed')
                ->where('metadata->idempotency_key', $idempotencyKey)
                ->latest('id')
                ->first();

            if ($existing) {
                return $this->runs->findOrFail($existing->id);
            }
        }

        return DB::transaction(function () use ($automationRule, $context, $triggeredBy): AutomationRun {
            $run = $this->runs->create([
                'school_id' => $automationRule->school_id,
                'automation_rule_id' => $automationRule->id,
                'status' => 'processing',
                'started_at' => now(),
                'completed_at' => null,
                'records_processed' => 0,
                'success_count' => 0,
                'failed_count' => 0,
                'error_message' => null,
                'metadata' => array_merge($context, [
                    'triggered_by' => $triggeredBy?->id,
                ]),
            ]);

            if (! $this->actions->evaluateConditions($automationRule->conditions, $context)) {
                return $this->runs->update($run, [
                    'status' => 'completed',
                    'completed_at' => now(),
                    'records_processed' => 0,
                    'success_count' => 0,
                    'failed_count' => 0,
                    'metadata' => array_merge($run->metadata ?? [], ['skipped' => true]),
                ]);
            }

            $successCount = 0;
            $failedCount = 0;

            foreach ($automationRule->actions as $action) {
                try {
                    $response = $this->actions->executeAutomationAction($action, array_merge($context, [
                        'school_id' => $automationRule->school_id,
                        'automation_rule' => $automationRule,
                        'automation_run' => $run,
                    ]));

                    $this->runs->createActionLog([
                        'school_id' => $automationRule->school_id,
                        'automation_run_id' => $run->id,
                        'automation_rule_id' => $automationRule->id,
                        'action_type' => $action['type'],
                        'reference_type' => $context['reference_type'] ?? null,
                        'reference_id' => $context['reference_id'] ?? null,
                        'status' => 'success',
                        'payload' => $action,
                        'response' => $response,
                        'error_message' => null,
                        'executed_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Throwable $throwable) {
                    $this->runs->createActionLog([
                        'school_id' => $automationRule->school_id,
                        'automation_run_id' => $run->id,
                        'automation_rule_id' => $automationRule->id,
                        'action_type' => $action['type'] ?? 'task',
                        'reference_type' => $context['reference_type'] ?? null,
                        'reference_id' => $context['reference_id'] ?? null,
                        'status' => 'failed',
                        'payload' => $action,
                        'response' => null,
                        'error_message' => $throwable->getMessage(),
                        'executed_at' => now(),
                    ]);

                    $failedCount++;
                }
            }

            $run = $this->runs->update($run, [
                'status' => $failedCount > 0 && $successCount === 0 ? 'failed' : 'completed',
                'completed_at' => now(),
                'records_processed' => max($successCount + $failedCount, 1),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'error_message' => $failedCount > 0 ? 'One or more automation actions failed.' : null,
            ]);

            DB::afterCommit(fn () => event(new AutomationRuleTriggered($automationRule, $run, $context)));

            return $run;
        });
    }
}
