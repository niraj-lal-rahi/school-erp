<?php

namespace App\Contracts\Communication;

use App\Models\Communication\NotificationLog;

interface EmailProviderInterface
{
    public function send(NotificationLog $notificationLog, mixed $recipient): array;
}
