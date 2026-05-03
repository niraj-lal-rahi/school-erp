<?php

namespace App\Jobs\Workflows;

use App\Services\Workflows\WorkflowRuntimeService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExecuteWorkflowStepJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $workflowInstanceId,
    ) {
    }

    public function handle(WorkflowRuntimeService $workflows): void
    {
        $workflows->advance($workflows->findOrFail($this->workflowInstanceId));
    }
}
