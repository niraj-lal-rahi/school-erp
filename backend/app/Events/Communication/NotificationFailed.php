<?php

namespace App\Events\Communication;

use App\Models\Communication\NotificationLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationFailed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public NotificationLog $notificationLog,
        public string $errorMessage,
    ) {
    }
}
