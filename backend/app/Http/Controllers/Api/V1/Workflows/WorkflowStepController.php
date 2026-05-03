<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\StoreWorkflowStepRequest;
use App\Http\Resources\Workflows\WorkflowStepResource;
use App\Models\Workflows\WorkflowDefinition;
use App\Models\Workflows\WorkflowStep;
use App\Services\Workflows\WorkflowDefinitionService;
use Illuminate\Http\JsonResponse;

class WorkflowStepController extends Controller
{
    public function __construct(
        protected WorkflowDefinitionService $definitions,
    ) {
    }

    public function store(StoreWorkflowStepRequest $request, WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('update', $workflowDefinition);
        $workflowStep = $this->definitions->addStep($workflowDefinition, [
            ...$request->validated(),
            'workflow_definition_id' => $workflowDefinition->id,
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Workflow step created successfully.',
            'data' => new WorkflowStepResource($workflowStep),
        ], 201);
    }

    public function update(StoreWorkflowStepRequest $request, WorkflowStep $workflowStep): JsonResponse
    {
        $this->authorize('update', $workflowStep);
        $workflowStep = $this->definitions->updateStep($workflowStep, $request->validated());

        return response()->json([
            'message' => 'Workflow step updated successfully.',
            'data' => new WorkflowStepResource($workflowStep),
        ]);
    }

    public function destroy(WorkflowStep $workflowStep): JsonResponse
    {
        $this->authorize('delete', $workflowStep);
        $this->definitions->deleteStep($workflowStep);

        return response()->json(null, 204);
    }
}
