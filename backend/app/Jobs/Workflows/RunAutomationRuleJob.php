<?php

namespace App\Jobs\Workflows;

use App\Services\Workflows\AutomationExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunAutomationRuleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $automationRuleId,
        public array $context = [],
        public ?int $triggeredBy = null,
    ) {
    }

    public function handle(AutomationExecutionService $automations): void
    {
        $automations->execute($this->automationRuleId, $this->context);
    }
}
