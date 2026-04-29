<?php

namespace App\Listeners\Reports;

use App\Contracts\Communication\EmailProviderInterface;
use App\Events\Reports\ReportRunCompleted;
use App\Repositories\Contracts\Communication\NotificationRepositoryInterface;
use App\Services\Communication\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;

class SendScheduledReport
{
    public function __construct(
        protected NotificationService $notifications,
        protected NotificationRepositoryInterface $notificationLogs,
        protected EmailProviderInterface $emailProvider,
    ) {
    }

    public function handle(ReportRunCompleted $event): void
    {
        if (($event->context['run_type'] ?? $event->reportRun->run_type) !== 'scheduled') {
            return;
        }

        $recipients = collect($event->context['recipients'] ?? []);
        if ($recipients->isEmpty()) {
            return;
        }

        $channel = $event->context['channel'] ?? 'in_app';
        $subject = 'Scheduled report ready: '.($event->context['report_name'] ?? $event->reportRun->reportDefinition?->name ?? 'Report');
        $message = $this->buildMessage($event);

        $internalRecipients = $recipients
            ->filter(fn ($recipient) => isset($recipient['user_type'], $recipient['user_id']))
            ->map(function (array $recipient) use ($event): array {
                return [
                    'recipient_type' => $recipient['user_type'],
                    'recipient_id' => (int) $recipient['user_id'],
                    'recipient' => new Fluent(['school_id' => $event->reportRun->school_id]),
                ];
            })
            ->values();

        if ($internalRecipients->isNotEmpty()) {
            $this->notifications->sendToRecipients($internalRecipients, [
                'school_id' => $event->reportRun->school_id,
                'channel' => $channel,
                'channels' => $channel === 'email' ? ['email', 'in_app'] : [$channel],
                'subject' => $subject,
                'message' => $message,
                'provider' => $channel === 'email' ? 'scheduled-report-email' : 'scheduled-report',
            ]);
        }

        if ($channel === 'email') {
            $this->sendExternalEmails(
                $recipients->filter(fn ($recipient) => ! empty($recipient['email']))->values(),
                $event,
                $subject,
                $message,
            );
        }
    }

    protected function sendExternalEmails(Collection $recipients, ReportRunCompleted $event, string $subject, string $message): void
    {
        foreach ($recipients as $recipient) {
            $notificationLog = $this->notificationLogs->create([
                'school_id' => $event->reportRun->school_id,
                'notifiable_type' => 'user',
                'notifiable_id' => 0,
                'channel' => 'email',
                'template_id' => null,
                'subject' => $subject,
                'message' => $message,
                'provider' => 'scheduled-report-email',
                'provider_message_id' => null,
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
                'delivered_at' => null,
                'read_at' => null,
            ]);

            try {
                $response = $this->emailProvider->send($notificationLog, new Fluent(['email' => $recipient['email']]));
                $this->notificationLogs->update($notificationLog, [
                    'provider' => $response['provider'] ?? 'scheduled-report-email',
                    'provider_message_id' => $response['provider_message_id'] ?? null,
                    'status' => $response['status'] ?? 'sent',
                    'sent_at' => now(),
                    'delivered_at' => now(),
                    'error_message' => null,
                ]);
            } catch (\Throwable $throwable) {
                $this->notificationLogs->update($notificationLog, [
                    'status' => 'failed',
                    'error_message' => $throwable->getMessage(),
                ]);
            }
        }
    }

    protected function buildMessage(ReportRunCompleted $event): string
    {
        $path = $event->context['export_file_path'] ?? $event->reportRun->file_path;

        return trim(implode("\n", array_filter([
            'Your scheduled report is ready.',
            'Report: '.($event->context['report_name'] ?? $event->reportRun->reportDefinition?->name ?? 'Report'),
            $path ? 'File: '.$path : null,
        ])));
    }
}
