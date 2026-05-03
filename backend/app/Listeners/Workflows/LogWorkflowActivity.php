<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\AutomationRuleTriggered;
use App\Events\Workflows\ReminderDue;
use App\Events\Workflows\WorkflowCompleted;
use App\Events\Workflows\WorkflowStarted;
use App\Events\Workflows\WorkflowStepApproved;
use App\Events\Workflows\WorkflowStepRejected;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogWorkflowActivity implements ShouldQueue
{
    public function handle(object $event): void
    {
        match (true) {
            $event instanceof WorkflowStarted => Log::info('Workflow started.', [
                'workflow_instance_id' => $event->workflowInstance->id,
                'workflow_definition_id' => $event->workflowInstance->workflow_definition_id,
                'school_id' => $event->workflowInstance->school_id,
            ]),
            $event instanceof WorkflowStepApproved => Log::info('Workflow step approved.', [
                'approval_request_id' => $event->approvalRequest->id,
                'workflow_instance_id' => $event->approvalRequest->workflow_instance_id,
                'school_id' => $event->approvalRequest->school_id,
            ]),
            $event instanceof WorkflowStepRejected => Log::warning('Workflow step rejected.', [
                'approval_request_id' => $event->approvalRequest->id,
                'workflow_instance_id' => $event->approvalRequest->workflow_instance_id,
                'school_id' => $event->approvalRequest->school_id,
            ]),
            $event instanceof WorkflowCompleted => Log::info('Workflow completed.', [
                'workflow_instance_id' => $event->workflowInstance->id,
                'school_id' => $event->workflowInstance->school_id,
            ]),
            $event instanceof AutomationRuleTriggered => Log::info('Automation rule triggered.', [
                'automation_rule_id' => $event->automationRule->id,
                'automation_run_id' => $event->automationRun->id,
                'school_id' => $event->automationRule->school_id,
            ]),
            $event instanceof ReminderDue => Log::info('Reminder due processed.', [
                'reminder_rule_id' => $event->reminderRule->id,
                'school_id' => $event->reminderRule->school_id,
                'recipient_type' => $event->target['recipient_type'] ?? null,
                'recipient_id' => $event->target['recipient_id'] ?? null,
            ]),
            default => null,
        };
    }
}
