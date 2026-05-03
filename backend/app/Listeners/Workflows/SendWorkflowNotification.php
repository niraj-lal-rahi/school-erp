<?php

namespace App\Listeners\Workflows;

use App\Events\Workflows\ReminderDue;
use App\Events\Workflows\WorkflowCompleted;
use App\Events\Workflows\WorkflowStarted;
use App\Events\Workflows\WorkflowStepApproved;
use App\Events\Workflows\WorkflowStepRejected;
use App\Models\User;
use App\Services\Communication\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class SendWorkflowNotification implements ShouldQueue
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function handle(object $event): void
    {
        [$schoolId, $subject, $message, $recipients] = $this->buildNotification($event);

        if (! $schoolId || ! $message || $recipients->isEmpty()) {
            return;
        }

        $this->notifications->sendToRecipients($recipients, [
            'school_id' => $schoolId,
            'subject' => $subject,
            'message' => $message,
            'channel' => 'in_app',
            'channels' => ['in_app'],
            'provider' => 'workflow',
        ]);
    }

    protected function buildNotification(object $event): array
    {
        return match (true) {
            $event instanceof WorkflowStarted => [
                $event->workflowInstance->school_id,
                'Workflow started',
                sprintf('Workflow %s has started.', $event->workflowInstance->workflowDefinition?->name ?? '#'.$event->workflowInstance->workflow_definition_id),
                $this->collectUsers([$event->workflowInstance->starter]),
            ],
            $event instanceof WorkflowStepApproved => [
                $event->approvalRequest->school_id,
                'Workflow step approved',
                sprintf('Approval for %s has been approved.', $event->approvalRequest->module),
                $this->collectUsers([$event->approvalRequest->requester]),
            ],
            $event instanceof WorkflowStepRejected => [
                $event->approvalRequest->school_id,
                'Workflow step rejected',
                sprintf('Approval for %s has been rejected.', $event->approvalRequest->module),
                $this->collectUsers([$event->approvalRequest->requester]),
            ],
            $event instanceof WorkflowCompleted => [
                $event->workflowInstance->school_id,
                'Workflow completed',
                sprintf('Workflow %s has completed.', $event->workflowInstance->workflowDefinition?->name ?? '#'.$event->workflowInstance->workflow_definition_id),
                $this->collectUsers([$event->workflowInstance->starter]),
            ],
            $event instanceof ReminderDue => [
                $event->reminderRule->school_id,
                'Reminder due',
                $event->target['message'] ?? $event->reminderRule->name,
                $this->collectRecipientsFromTarget($event->target),
            ],
            default => [null, null, null, collect()],
        };
    }

    protected function collectUsers(array $users): Collection
    {
        return collect($users)
            ->filter()
            ->map(fn (User $user) => [
                'recipient_type' => User::class,
                'recipient_id' => $user->id,
                'recipient' => $user,
            ])
            ->values();
    }

    protected function collectRecipientsFromTarget(array $target): Collection
    {
        $recipient = $target['recipient'] ?? null;
        if (! $recipient || ! isset($target['recipient_type'], $target['recipient_id'])) {
            return collect();
        }

        return collect([[
            'recipient_type' => $target['recipient_type'],
            'recipient_id' => $target['recipient_id'],
            'recipient' => $recipient,
        ]]);
    }
}
