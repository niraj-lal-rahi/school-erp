<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\ApproveWorkflowStepRequest;
use App\Http\Requests\Workflows\RejectWorkflowStepRequest;
use App\Http\Resources\Workflows\ApprovalRequestResource;
use App\Models\Workflows\ApprovalRequest;
use App\Services\Workflows\ApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalRequestController extends Controller
{
    public function __construct(
        protected ApprovalService $approvals,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ApprovalRequest::class);

        return response()->json([
            'data' => ApprovalRequestResource::collection($this->approvals->paginate(
                $request->only(['module', 'status', 'reference_type', 'reference_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function approve(ApproveWorkflowStepRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorize('approve', $approvalRequest);
        $approvalRequest = $this->approvals->approve($approvalRequest, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Approval processed successfully.',
            'data' => new ApprovalRequestResource($approvalRequest),
        ]);
    }

    public function reject(RejectWorkflowStepRequest $request, ApprovalRequest $approvalRequest): JsonResponse
    {
        $this->authorize('reject', $approvalRequest);
        $approvalRequest = $this->approvals->reject($approvalRequest, $request->user(), $request->validated());

        return response()->json([
            'message' => 'Rejection processed successfully.',
            'data' => new ApprovalRequestResource($approvalRequest),
        ]);
    }
}
