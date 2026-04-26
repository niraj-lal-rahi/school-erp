<?php

namespace App\Services\Communication\Providers;

use App\Contracts\Communication\EmailProviderInterface;
use App\Models\Communication\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogEmailProvider implements EmailProviderInterface
{
    public function send(NotificationLog $notificationLog, mixed $recipient): array
    {
        Log::info('communication.email.send', [
            'notification_log_id' => $notificationLog->id,
            'recipient_type' => $notificationLog->notifiable_type,
            'recipient_id' => $notificationLog->notifiable_id,
            'recipient_email' => $recipient?->email,
            'subject' => $notificationLog->subject,
        ]);

        return [
            'provider' => 'log-email',
            'provider_message_id' => (string) Str::uuid(),
            'status' => 'sent',
        ];
    }
}
