<?php

namespace App\Events\Workflows;

use App\Models\Workflows\WorkflowInstance;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public WorkflowInstance $workflowInstance,
    ) {
    }
}
