<?php

namespace App\Jobs\Communication;

use App\Contracts\Communication\PushProviderInterface;
use App\Events\Communication\NotificationFailed;
use App\Models\Communication\NotificationLog;
use App\Repositories\Contracts\Communication\NotificationRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendPushNotificationJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $notificationLogId,
    ) {
    }

    public function handle(
        NotificationRepositoryInterface $notifications,
        PushProviderInterface $provider,
    ): void {
        $log = NotificationLog::withoutGlobalScopes()->find($this->notificationLogId);

        if (! $log) {
            return;
        }

        $result = $provider->send($log, $log->resolveNotifiable());

        $notifications->update($log, [
            'provider' => $result['provider'] ?? $log->provider ?? 'log-push',
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
