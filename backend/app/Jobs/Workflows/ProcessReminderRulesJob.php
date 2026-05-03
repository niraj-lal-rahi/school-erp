<?php

namespace App\Jobs\Workflows;

use App\Services\Workflows\ReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReminderRulesJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $context = [],
    ) {
    }

    public function handle(ReminderService $reminders): void
    {
        $reminders->processDue($this->context);
    }
}
