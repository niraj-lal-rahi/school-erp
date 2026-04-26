<?php

namespace App\Services\Communication\Providers;

use App\Contracts\Communication\SmsProviderInterface;
use App\Models\Communication\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(NotificationLog $notificationLog, mixed $recipient): array
    {
        Log::info('communication.sms.send', [
            'notification_log_id' => $notificationLog->id,
            'recipient_type' => $notificationLog->notifiable_type,
            'recipient_id' => $notificationLog->notifiable_id,
            'recipient_phone' => $recipient?->phone,
            'subject' => $notificationLog->subject,
        ]);

        return [
            'provider' => 'log-sms',
            'provider_message_id' => (string) Str::uuid(),
            'status' => 'sent',
        ];
    }
}
