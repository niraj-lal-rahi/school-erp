<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\StartWorkflowRequest;
use App\Http\Resources\Workflows\WorkflowInstanceResource;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowInstance;
use App\Services\Workflows\WorkflowRuntimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowInstanceController extends Controller
{
    public function __construct(
        protected WorkflowRuntimeService $runtime,
    ) {
    }

    public function start(StartWorkflowRequest $request): JsonResponse
    {
        $definition = WorkflowDefinition::query()->findOrFail((int) $request->validated('workflow_definition_id'));
        $this->authorize('start', $definition);
        $workflowInstance = $this->runtime->start($request->validated(), $request->user());

        return response()->json([
            'message' => 'Workflow started successfully.',
            'data' => new WorkflowInstanceResource($workflowInstance),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkflowInstance::class);

        return response()->json([
            'data' => WorkflowInstanceResource::collection($this->runtime->paginate(
                $request->only(['module', 'status', 'reference_type', 'reference_id', 'workflow_definition_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function show(WorkflowInstance $workflowInstance): JsonResponse
    {
        $this->authorize('view', $workflowInstance);

        return response()->json([
            'data' => new WorkflowInstanceResource($this->runtime->findOrFail($workflowInstance->id)),
        ]);
    }

    public function cancel(Request $request, WorkflowInstance $workflowInstance): JsonResponse
    {
        $request->validate([
            'remarks' => ['nullable', 'string'],
        ]);

        $this->authorize('cancel', $workflowInstance);
        $workflowInstance = $this->runtime->cancel($workflowInstance, $request->user(), $request->input('remarks'));

        return response()->json([
            'message' => 'Workflow cancelled successfully.',
            'data' => new WorkflowInstanceResource($workflowInstance),
        ]);
    }
}
