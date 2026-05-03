<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Models\Workflows\ApprovalRequest;
use App\Models\Workflows\AutomationRun;
use App\Models\Workflows\WorkflowInstance;
use App\Services\Workflows\ApprovalService;
use App\Services\Workflows\AutomationExecutionService;
use App\Services\Workflows\WorkflowRuntimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowReportController extends Controller
{
    public function __construct(
        protected WorkflowRuntimeService $runtime,
        protected AutomationExecutionService $automationExecution,
        protected ApprovalService $approvals,
    ) {
    }

    public function workflowSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkflowInstance::class);

        return response()->json([
            'data' => $this->runtime->summary(
                $request->only(['school_id', 'module', 'status', 'reference_type', 'reference_id', 'date_from', 'date_to']),
            ),
        ]);
    }

    public function automationSummary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AutomationRun::class);

        return response()->json([
            'data' => $this->automationExecution->summary(
                $request->only(['school_id', 'module', 'status', 'trigger_type', 'date_from', 'date_to']),
            ),
        ]);
    }

    public function approvalPending(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        return response()->json([
            'data' => $this->approvals->pendingSummary(
                $request->only(['school_id', 'module', 'reference_type', 'reference_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }
}
