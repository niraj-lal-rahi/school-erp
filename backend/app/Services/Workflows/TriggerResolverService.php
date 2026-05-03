<?php

namespace App\Services\Workflows;

use App\Repositories\Contracts\Workflows\AutomationRuleRepositoryInterface;
use App\Repositories\Contracts\Workflows\WorkflowDefinitionRepositoryInterface;
use Illuminate\Support\Collection;

class TriggerResolverService
{
    public function __construct(
        protected WorkflowDefinitionRepositoryInterface $definitions,
        protected AutomationRuleRepositoryInterface $automationRules,
        protected WorkflowRuntimeService $runtime,
        protected AutomationExecutionService $automationExecution,
    ) {
    }

    public function resolve(string $eventName, array $payload = []): array
    {
        return [
            'workflows' => $this->startMatchingWorkflows($eventName, $payload),
            'automations' => $this->triggerMatchingAutomations($eventName, $payload),
        ];
    }

    public function startMatchingWorkflows(string $eventName, array $payload = []): Collection
    {
        return $this->definitions->allActiveByTrigger('event', $eventName)
            ->map(function ($workflowDefinition) use ($payload) {
                return $this->runtime->start([
                    'workflow_definition_id' => $workflowDefinition->id,
                    'reference_type' => $payload['reference_type'] ?? ($payload['model_class'] ?? 'generic.reference'),
                    'reference_id' => (int) ($payload['reference_id'] ?? 0),
                    'metadata' => $payload,
                    'started_by' => $payload['started_by'] ?? null,
                ]);
            });
    }

    public function triggerMatchingAutomations(string $eventName, array $payload = []): Collection
    {
        return $this->automationRules->allActiveByTrigger('event', $eventName)
            ->map(function ($automationRule) use ($eventName, $payload) {
                return $this->automationExecution->execute($automationRule, array_merge($payload, [
                    'trigger_event' => $eventName,
                ]));
            });
    }

    public function dueScheduledAutomations(): Collection
    {
        return $this->automationRules->dueScheduledRules();
    }
}
