<?php

namespace App\Jobs\Communication;

use App\Contracts\Communication\EmailProviderInterface;
use App\Events\Communication\NotificationFailed;
use App\Models\Communication\NotificationLog;
use App\Repositories\Contracts\Communication\NotificationRepositoryInterface;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendEmailNotificationJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 90;

    public function __construct(
        public int $notificationLogId,
    ) {
        $this->onQueue(config('queue.routing.notifications', QueueNames::NOTIFICATIONS));
    }

    public function backoff(): array
    {
        return JobRetryProfile::notifications();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('notification-email:'.$this->notificationLogId))
                ->releaseAfter(15)
                ->expireAfter(180),
        ];
    }

    public function handle(
        NotificationRepositoryInterface $notifications,
        EmailProviderInterface $provider,
    ): void {
        $log = NotificationLog::withoutGlobalScopes()->find($this->notificationLogId);

        if (! $log || in_array($log->status, ['sent', 'delivered'], true)) {
            return;
        }

        $result = $provider->send($log, $log->resolveNotifiable());

        $notifications->update($log, [
            'provider' => $result['provider'] ?? $log->provider ?? 'log-email',
            'provider_message_id' => $result['provider_message_id'] ?? null,
            'status' => $result['status'] ?? 'sent',
            'sent_at' => now(),
            'delivered_at' => ($result['status'] ?? 'sent') === 'delivered' ? now() : null,
            'error_message' => null,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $log = NotificationLog::withoutGlobalScopes()->find($this->notificationLogId);

        if (! $log) {
            return;
        }

        $log->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);

        event(new NotificationFailed($log->refresh(), $exception->getMessage()));
    }
}
