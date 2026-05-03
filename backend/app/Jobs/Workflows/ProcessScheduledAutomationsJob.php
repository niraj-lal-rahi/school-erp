<?php

namespace App\Jobs\Workflows;

use App\Services\Workflows\TriggerResolverService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessScheduledAutomationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(TriggerResolverService $triggers): void
    {
        foreach ($triggers->dueScheduledAutomations() as $rule) {
            RunAutomationRuleJob::dispatch($rule->id, [
                'trigger_type' => 'schedule',
                'scheduled_at' => now()->toISOString(),
            ]);
        }
    }
}
