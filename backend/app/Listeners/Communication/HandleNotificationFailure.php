<?php

namespace App\Listeners\Communication;

use App\Events\Communication\NotificationFailed;
use Illuminate\Support\Facades\Log;

class HandleNotificationFailure
{
    public function handle(NotificationFailed $event): void
    {
        Log::warning('communication.notification.failed', [
            'notification_log_id' => $event->notificationLog->id,
            'channel' => $event->notificationLog->channel,
            'status' => $event->notificationLog->status,
            'error_message' => $event->errorMessage,
        ]);
    }
}
