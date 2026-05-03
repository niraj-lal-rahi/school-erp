<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\StoreWorkflowDefinitionRequest;
use App\Http\Requests\Workflows\UpdateWorkflowDefinitionRequest;
use App\Http\Resources\Workflows\WorkflowDefinitionResource;
use App\Models\Workflows\WorkflowDefinition;
use App\Services\Workflows\WorkflowDefinitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowDefinitionController extends Controller
{
    public function __construct(
        protected WorkflowDefinitionService $definitions,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkflowDefinition::class);

        return response()->json([
            'data' => WorkflowDefinitionResource::collection($this->definitions->paginate(
                $request->only(['search', 'module', 'status', 'trigger_type', 'trigger_event', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreWorkflowDefinitionRequest $request): JsonResponse
    {
        $this->authorize('create', WorkflowDefinition::class);
        $workflowDefinition = $this->definitions->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Workflow definition created successfully.',
            'data' => new WorkflowDefinitionResource($workflowDefinition),
        ], 201);
    }

    public function show(WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('view', $workflowDefinition);

        return response()->json([
            'data' => new WorkflowDefinitionResource($this->definitions->findOrFail($workflowDefinition->id)),
        ]);
    }

    public function update(UpdateWorkflowDefinitionRequest $request, WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('update', $workflowDefinition);
        $workflowDefinition = $this->definitions->update($workflowDefinition, $request->validated());

        return response()->json([
            'message' => 'Workflow definition updated successfully.',
            'data' => new WorkflowDefinitionResource($workflowDefinition),
        ]);
    }

    public function destroy(WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('delete', $workflowDefinition);
        $this->definitions->delete($workflowDefinition);

        return response()->json(null, 204);
    }

    public function activate(WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('activate', $workflowDefinition);
        $workflowDefinition = $this->definitions->activate($workflowDefinition);

        return response()->json([
            'message' => 'Workflow definition activated successfully.',
            'data' => new WorkflowDefinitionResource($workflowDefinition),
        ]);
    }

    public function deactivate(WorkflowDefinition $workflowDefinition): JsonResponse
    {
        $this->authorize('deactivate', $workflowDefinition);
        $workflowDefinition = $this->definitions->deactivate($workflowDefinition);

        return response()->json([
            'message' => 'Workflow definition deactivated successfully.',
            'data' => new WorkflowDefinitionResource($workflowDefinition),
        ]);
    }
}
