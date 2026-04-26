<?php

namespace App\Services\Communication;

use App\Models\Communication\NotificationLog;
use App\Models\Communication\NotificationPreference;
use App\Repositories\Contracts\Communication\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $notifications,
    ) {
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->notifications->paginate($filters, $perPage);
    }

    public function findOrFail(int $id): NotificationLog
    {
        return $this->notifications->findOrFail($id);
    }

    public function sendToRecipients(Collection $recipients, array $payload): Collection
    {
        $channels = $this->normalizeChannels($payload);
        $results = collect();

        foreach ($recipients as $recipient) {
            foreach ($channels as $channel) {
                if (! $this->channelEnabledForRecipient($recipient, $channel)) {
                    continue;
                }

                $results->push($this->queueNotification($recipient, $channel, $payload));
            }
        }

        return $results;
    }

    public function markAsRead(NotificationLog $notificationLog): NotificationLog
    {
        return DB::transaction(fn (): NotificationLog => $this->notifications->update($notificationLog, [
            'status' => 'read',
            'read_at' => now(),
        ]));
    }

    public function createInAppNotification(array $recipient, array $payload): NotificationLog
    {
        return DB::transaction(fn (): NotificationLog => $this->notifications->create([
            'school_id' => $payload['school_id'],
            'notifiable_type' => $recipient['recipient_type'],
            'notifiable_id' => $recipient['recipient_id'],
            'channel' => 'in_app',
            'template_id' => $payload['template_id'] ?? null,
            'subject' => $payload['subject'] ?? null,
            'message' => $payload['message'],
            'provider' => 'in_app',
            'provider_message_id' => null,
            'status' => 'sent',
            'error_message' => null,
            'sent_at' => now(),
            'delivered_at' => now(),
            'read_at' => null,
        ]));
    }

    protected function queueNotification(array $recipient, string $channel, array $payload): NotificationLog
    {
        if ($channel === 'in_app') {
            return $this->createInAppNotification($recipient, $payload);
        }

        $log = DB::transaction(fn (): NotificationLog => $this->notifications->create([
            'school_id' => $payload['school_id'],
            'notifiable_type' => $recipient['recipient_type'],
            'notifiable_id' => $recipient['recipient_id'],
            'channel' => $channel,
            'template_id' => $payload['template_id'] ?? null,
            'subject' => $payload['subject'] ?? null,
            'message' => $payload['message'],
            'provider' => $payload['provider'] ?? null,
            'provider_message_id' => null,
            'status' => 'pending',
            'error_message' => null,
            'sent_at' => null,
            'delivered_at' => null,
            'read_at' => null,
        ]));

        $this->dispatchChannelJob($channel, $log->id);

        return $this->notifications->findOrFail($log->id);
    }

    protected function normalizeChannels(array $payload): array
    {
        $channels = $payload['channels'] ?? [];

        if (($payload['channel'] ?? null) && $payload['channel'] !== 'multi') {
            $channels[] = $payload['channel'];
        }

        if ($payload['channel'] ?? null === 'multi' && $channels === []) {
            $channels = ['email', 'sms', 'push', 'in_app'];
        }

        return collect($channels)->filter()->unique()->values()->all();
    }

    protected function channelEnabledForRecipient(array $recipient, string $channel): bool
    {
        $preference = NotificationPreference::query()
            ->where('school_id', $recipient['recipient']->school_id ?? null)
            ->where('user_type', $recipient['recipient_type'])
            ->where('user_id', $recipient['recipient_id'])
            ->first();

        if (! $preference) {
            return true;
        }

        if ($this->withinQuietHours($preference)) {
            return false;
        }

        return match ($channel) {
            'email' => (bool) $preference->email_enabled,
            'sms' => (bool) $preference->sms_enabled,
            'push' => (bool) $preference->push_enabled,
            'in_app' => (bool) $preference->in_app_enabled,
            default => true,
        };
    }

    protected function withinQuietHours(NotificationPreference $preference): bool
    {
        if (! $preference->quiet_hours_start || ! $preference->quiet_hours_end) {
            return false;
        }

        $now = now()->format('H:i:s');
        $start = $preference->quiet_hours_start->format('H:i:s');
        $end = $preference->quiet_hours_end->format('H:i:s');

        if ($start <= $end) {
            return $now >= $start && $now <= $end;
        }

        return $now >= $start || $now <= $end;
    }

    protected function dispatchChannelJob(string $channel, int $notificationLogId): void
    {
        $jobs = [
            'email' => 'App\\Jobs\\Communication\\SendEmailNotificationJob',
            'sms' => 'App\\Jobs\\Communication\\SendSmsNotificationJob',
            'push' => 'App\\Jobs\\Communication\\SendPushNotificationJob',
        ];

        $jobClass = $jobs[$channel] ?? null;

        if ($jobClass && class_exists($jobClass)) {
            dispatch(new $jobClass($notificationLogId));
        }
    }
}
