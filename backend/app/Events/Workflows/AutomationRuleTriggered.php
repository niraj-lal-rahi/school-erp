<?php

namespace App\Events\Workflows;

use App\Models\Workflows\AutomationRule;
use App\Models\Workflows\AutomationRun;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AutomationRuleTriggered
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public AutomationRule $automationRule,
        public AutomationRun $automationRun,
        public array $context = [],
    ) {
    }
}
