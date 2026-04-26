<?php

namespace App\Contracts\Communication;

use App\Models\Communication\NotificationLog;

interface PushProviderInterface
{
    public function send(NotificationLog $notificationLog, mixed $recipient): array;
}
