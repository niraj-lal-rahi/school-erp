<?php

namespace App\Events\Workflows;

use App\Models\Workflows\ReminderRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReminderDue
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ReminderRule $reminderRule,
        public array $target = [],
    ) {
    }
}
