<?php

namespace App\Jobs\Communication;

use App\Services\Communication\ScheduledMessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledMessagesJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $schoolId = null,
    ) {
    }

    public function handle(ScheduledMessageService $service): void
    {
        $service->processDueScheduledMessages($this->schoolId);
    }
}
