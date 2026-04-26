<?php

namespace App\Services\Communication\Providers;

use App\Contracts\Communication\PushProviderInterface;
use App\Models\Communication\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogPushProvider implements PushProviderInterface
{
    public function send(NotificationLog $notificationLog, mixed $recipient): array
    {
        Log::info('communication.push.send', [
            'notification_log_id' => $notificationLog->id,
            'recipient_type' => $notificationLog->notifiable_type,
            'recipient_id' => $notificationLog->notifiable_id,
            'subject' => $notificationLog->subject,
        ]);

        return [
            'provider' => 'log-push',
            'provider_message_id' => (string) Str::uuid(),
            'status' => 'sent',
        ];
    }
}
