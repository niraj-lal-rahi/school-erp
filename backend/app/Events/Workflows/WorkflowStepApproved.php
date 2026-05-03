<?php

namespace App\Events\Workflows;

use App\Models\Workflows\ApprovalRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowStepApproved
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ApprovalRequest $approvalRequest,
    ) {
    }
}
