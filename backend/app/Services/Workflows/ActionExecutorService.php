<?php

namespace App\Services\Workflows;

use App\Models\Admission;
use App\Models\Finance\FeeInvoice;
use App\Models\HR\StaffLeaveApplication;
use App\Models\Student;
use App\Models\User;
use App\Models\Workflows\WorkflowStep;
use App\Services\Communication\NotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class ActionExecutorService
{
    public function __construct(
        protected NotificationService $notifications,
    ) {
    }

    public function executeStep(WorkflowStep $step, array $context = []): array
    {
        return match ($step->step_type) {
            'notification' => $this->executeNotificationAction([
                'type' => 'in_app',
                'channel' => $step->config['channel'] ?? 'in_app',
                'channels' => $step->config['channels'] ?? [$step->config['channel'] ?? 'in_app'],
                'subject' => $step->config['subject'] ?? $step->step_name,
                'message' => $step->config['message'] ?? $step->step_name,
                'recipient_type' => $step->config['recipient_type'] ?? 'user',
                'recipient_id' => $step->assigned_user_id ?? ($context['workflow_instance']?->started_by),
                'template_id' => $step->config['template_id'] ?? null,
            ], $context),
            'action' => $this->executeAutomationAction($step->config['action'] ?? ['type' => 'task'], $context),
            default => [
                'status' => 'skipped',
                'message' => 'No executable action configured for this step type.',
            ],
        };
    }

    public function executeAutomationAction(array $action, array $context = []): array
    {
        return match ($action['type'] ?? null) {
            'email', 'sms', 'push', 'in_app', 'reminder' => $this->executeNotificationAction($action, $context),
            'status_update' => $this->executeStatusUpdateAction($action, $context),
            'webhook' => $this->executeWebhookAction($action, $context),
            'task' => $this->executeTaskAction($action, $context),
            default => throw ValidationException::withMessages([
                'action' => ['Unsupported workflow action type.'],
            ]),
        };
    }

    public function evaluateConditions(null|array $conditions, array $context = []): bool
    {
        if ($conditions === null || $conditions === []) {
            return true;
        }

        if (isset($conditions['all']) && is_array($conditions['all'])) {
            foreach ($conditions['all'] as $condition) {
                if (! $this->evaluateConditionNode($condition, $context)) {
                    return false;
                }
            }

            return true;
        }

        if (isset($conditions['any']) && is_array($conditions['any'])) {
            foreach ($conditions['any'] as $condition) {
                if ($this->evaluateConditionNode($condition, $context)) {
                    return true;
                }
            }

            return false;
        }

        return $this->evaluateConditionNode($conditions, $context);
    }

    protected function executeNotificationAction(array $action, array $context): array
    {
        $recipient = $this->resolveRecipient(
            $action['recipient_type'] ?? 'user',
            $action['recipient_id'] ?? null,
            $context
        );

        $payload = [
            'school_id' => $context['school_id'] ?? $recipient?->school_id,
            'template_id' => $action['template_id'] ?? null,
            'channel' => $action['channel'] ?? ($action['type'] === 'reminder' ? 'in_app' : $action['type']),
            'channels' => $action['channels'] ?? [($action['channel'] ?? ($action['type'] === 'reminder' ? 'in_app' : $action['type']))],
            'subject' => $action['subject'] ?? 'Workflow Notification',
            'message' => $this->interpolate($action['message'] ?? 'Workflow notification.', $context),
            'provider' => $action['provider'] ?? null,
        ];

        $logs = $this->notifications->sendToRecipients(collect([[
            'recipient_type' => $action['recipient_type'] ?? 'user',
            'recipient_id' => $recipient->id,
            'recipient' => $recipient,
        ]]), $payload);

        return [
            'status' => 'success',
            'notifications_count' => $logs->count(),
        ];
    }

    protected function executeStatusUpdateAction(array $action, array $context): array
    {
        $reference = $context['reference'] ?? null;
        if (! $reference) {
            throw ValidationException::withMessages([
                'reference' => ['A reference record is required for status update actions.'],
            ]);
        }

        $field = $action['status_field'] ?? 'status';
        $value = $action['status_value'] ?? null;

        if (! $field || $value === null) {
            throw ValidationException::withMessages([
                'action' => ['status_field and status_value are required for status update actions.'],
            ]);
        }

        $updates = [$field => $value];

        if ($reference instanceof Admission) {
            $updates['reviewed_at'] = now();
            $updates['reviewed_by'] = $context['workflow_instance']?->started_by ?? null;
        }

        if ($reference instanceof StaffLeaveApplication) {
            $updates['reviewed_at'] = now();
            $updates['reviewed_by'] = $context['workflow_instance']?->started_by ?? null;
            $updates['review_remarks'] = $action['remarks'] ?? null;
        }

        $reference->update($updates);

        return [
            'status' => 'success',
            'updated_field' => $field,
            'updated_value' => $value,
        ];
    }

    protected function executeWebhookAction(array $action, array $context): array
    {
        $url = $action['url'] ?? $action['webhook_url'] ?? null;
        if (! $url) {
            throw ValidationException::withMessages([
                'action' => ['A webhook URL is required for webhook actions.'],
            ]);
        }

        $response = Http::timeout((int) ($action['timeout'] ?? 10))
            ->withHeaders($action['headers'] ?? [])
            ->post($url, [
                'school_id' => $context['school_id'] ?? null,
                'reference_type' => $context['workflow_instance']?->reference_type ?? ($context['reference_type'] ?? null),
                'reference_id' => $context['workflow_instance']?->reference_id ?? ($context['reference_id'] ?? null),
                'payload' => $context,
            ]);

        return [
            'status' => $response->successful() ? 'success' : 'failed',
            'http_status' => $response->status(),
            'body' => $response->json() ?? $response->body(),
        ];
    }

    protected function executeTaskAction(array $action, array $context): array
    {
        return [
            'status' => 'success',
            'task' => [
                'title' => $action['title'] ?? 'Workflow Task',
                'description' => $this->interpolate($action['description'] ?? 'Task placeholder created by workflow automation.', $context),
                'assignee_id' => $action['assignee_id'] ?? null,
            ],
        ];
    }

    protected function evaluateConditionNode(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $expected = $condition['value'] ?? null;

        if (! $field) {
            return true;
        }

        $actual = data_get($context, $field);

        return match ($operator) {
            '=', '==' => $actual == $expected,
            '!=', '<>' => $actual != $expected,
            '>' => $actual > $expected,
            '>=' => $actual >= $expected,
            '<' => $actual < $expected,
            '<=' => $actual <= $expected,
            'in' => in_array($actual, (array) $expected, true),
            'not_in' => ! in_array($actual, (array) $expected, true),
            'contains' => str_contains((string) $actual, (string) $expected),
            default => false,
        };
    }

    protected function resolveRecipient(string $recipientType, mixed $recipientId, array $context): mixed
    {
        if ($recipientId) {
            return match ($recipientType) {
                'student' => Student::query()->findOrFail((int) $recipientId),
                'user' => User::query()->findOrFail((int) $recipientId),
                'guardian' => \App\Models\Guardian::query()->findOrFail((int) $recipientId),
                'staff' => \App\Models\HR\Staff::query()->findOrFail((int) $recipientId),
                default => User::query()->findOrFail((int) $recipientId),
            };
        }

        $reference = $context['reference'] ?? null;

        if ($recipientType === 'student' && $reference instanceof FeeInvoice && $reference->student) {
            return $reference->student;
        }

        if ($recipientType === 'student' && $reference instanceof Admission && $reference->student) {
            return $reference->student;
        }

        if ($recipientType === 'user' && ($context['workflow_instance']?->starter)) {
            return $context['workflow_instance']->starter;
        }

        throw ValidationException::withMessages([
            'recipient' => ['Unable to resolve a valid recipient for the workflow action.'],
        ]);
    }

    protected function interpolate(string $message, array $context): string
    {
        return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function (array $matches) use ($context): string {
            return (string) data_get($context, $matches[1], '');
        }, $message) ?? $message;
    }
}
