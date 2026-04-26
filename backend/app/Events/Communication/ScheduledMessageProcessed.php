<?php

namespace App\Events\Communication;

use App\Models\Communication\ScheduledMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduledMessageProcessed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ScheduledMessage $scheduledMessage,
    ) {
    }
}
