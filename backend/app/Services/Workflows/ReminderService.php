<?php

namespace App\Services\Workflows;

use App\Events\Workflows\ReminderDue;
use App\Models\Finance\FeeInvoice;
use App\Models\Student;
use App\Models\Workflows\ReminderRule;
use App\Repositories\Contracts\Workflows\ReminderRuleRepositoryInterface;
use App\Services\Communication\NotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    public function __construct(
        protected ReminderRuleRepositoryInterface $rules,
        protected NotificationService $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->rules->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): ReminderRule
    {
        return $this->rules->findOrFail($id);
    }

    public function create(array $attributes): ReminderRule
    {
        return DB::transaction(fn (): ReminderRule => $this->rules->create($attributes));
    }

    public function update(ReminderRule $reminderRule, array $attributes): ReminderRule
    {
        return DB::transaction(fn (): ReminderRule => $this->rules->update($reminderRule, $attributes));
    }

    public function delete(ReminderRule $reminderRule): void
    {
        DB::transaction(function () use ($reminderRule): void {
            $this->rules->delete($reminderRule);
        });
    }

    public function processDue(array $context = []): array
    {
        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach ($this->rules->activeRules() as $rule) {
            foreach ($this->resolveTargets($rule, $context) as $target) {
                $processed++;

                try {
                    $this->sendReminder($rule, $target);
                    $sent++;
                } catch (\Throwable $throwable) {
                    $failed++;

                    $this->rules->createLog([
                        'school_id' => $rule->school_id,
                        'reminder_rule_id' => $rule->id,
                        'recipient_type' => $target['recipient_type'],
                        'recipient_id' => $target['recipient_id'],
                        'reference_type' => $target['reference_type'] ?? null,
                        'reference_id' => $target['reference_id'] ?? null,
                        'channel' => $rule->channel,
                        'status' => 'failed',
                        'sent_at' => null,
                        'error_message' => $throwable->getMessage(),
                    ]);
                }
            }
        }

        return [
            'processed' => $processed,
            'sent' => $sent,
            'failed' => $failed,
        ];
    }

    protected function sendReminder(ReminderRule $rule, array $target): void
    {
        event(new ReminderDue($rule, $target));

        $payload = [
            'school_id' => $rule->school_id,
            'template_id' => $rule->template_id,
            'channel' => $rule->channel,
            'channels' => $rule->channel === 'multi' ? ['email', 'sms', 'push', 'in_app'] : [$rule->channel],
            'subject' => $target['subject'] ?? $rule->name,
            'message' => $target['message'] ?? ($rule->template?->body ?? $rule->name),
        ];

        $this->notifications->sendToRecipients(collect([[
            'recipient_type' => $target['recipient_type'],
            'recipient_id' => $target['recipient_id'],
            'recipient' => $target['recipient'],
        ]]), $payload);

        $this->rules->createLog([
            'school_id' => $rule->school_id,
            'reminder_rule_id' => $rule->id,
            'recipient_type' => $target['recipient_type'],
            'recipient_id' => $target['recipient_id'],
            'reference_type' => $target['reference_type'] ?? null,
            'reference_id' => $target['reference_id'] ?? null,
            'channel' => $rule->channel,
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    protected function resolveTargets(ReminderRule $rule, array $context): Collection
    {
        if ($rule->module !== 'fees') {
            return collect($context['targets'] ?? []);
        }

        $today = now()->toDateString();
        $offset = (int) ($rule->offset_days ?? 0);

        $invoices = FeeInvoice::withoutGlobalScopes()
            ->with(['student.guardians'])
            ->where('school_id', $rule->school_id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->when($rule->reminder_type === 'before_due', fn ($query) => $query->whereDate('due_date', '=', now()->addDays($offset)->toDateString()))
            ->when($rule->reminder_type === 'after_due', fn ($query) => $query->whereDate('due_date', '=', now()->subDays($offset)->toDateString()))
            ->when($rule->reminder_type === 'recurring', fn ($query) => $query->whereDate('due_date', '<=', $today))
            ->when($rule->reminder_type === 'one_time', fn ($query) => $query->whereDate('due_date', '=', $today))
            ->get();

        return $invoices->flatMap(function (FeeInvoice $invoice): Collection {
            $student = $invoice->student;
            if (! $student) {
                return collect();
            }

            $message = sprintf(
                'Fee reminder for invoice %s. Outstanding balance: %s.',
                $invoice->invoice_no,
                number_format((float) $invoice->balance_amount, 2)
            );

            $targets = collect([[
                'recipient_type' => 'student',
                'recipient_id' => $student->id,
                'recipient' => $student,
                'reference_type' => FeeInvoice::class,
                'reference_id' => $invoice->id,
                'subject' => 'Fee Reminder',
                'message' => $message,
            ]]);

            return $targets->merge($student->guardians->map(fn ($guardian) => [
                'recipient_type' => 'guardian',
                'recipient_id' => $guardian->id,
                'recipient' => $guardian,
                'reference_type' => FeeInvoice::class,
                'reference_id' => $invoice->id,
                'subject' => 'Fee Reminder',
                'message' => $message,
            ]));
        });
    }
}
