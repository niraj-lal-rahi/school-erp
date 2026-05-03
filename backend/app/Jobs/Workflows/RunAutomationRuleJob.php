<?php

namespace App\Jobs\Workflows;

use App\Services\Workflows\AutomationExecutionService;
use App\Support\Queue\JobRetryProfile;
use App\Support\Queue\QueueNames;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class RunAutomationRuleJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(
        public int $automationRuleId,
        public array $context = [],
        public ?int $triggeredBy = null,
    ) {
        $this->onQueue(config('queue.routing.automation', QueueNames::AUTOMATION));
    }

    public function backoff(): array
    {
        return JobRetryProfile::automation();
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('automation-rule:'.$this->automationRuleId.':'.md5(json_encode($this->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))))
                ->releaseAfter(30)
                ->expireAfter(600),
        ];
    }

    public function handle(AutomationExecutionService $automations): void
    {
        $automations->execute($this->automationRuleId, $this->context);
    }
}
