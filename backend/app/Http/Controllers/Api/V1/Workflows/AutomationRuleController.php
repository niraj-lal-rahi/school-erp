<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\RunAutomationRuleRequest;
use App\Http\Requests\Workflows\StoreAutomationRuleRequest;
use App\Http\Requests\Workflows\UpdateAutomationRuleRequest;
use App\Http\Resources\Workflows\AutomationRuleResource;
use App\Http\Resources\Workflows\AutomationRunResource;
use App\Models\Workflows\AutomationRule;
use App\Services\Workflows\AutomationExecutionService;
use App\Services\Workflows\AutomationRuleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationRuleController extends Controller
{
    public function __construct(
        protected AutomationRuleService $rules,
        protected AutomationExecutionService $execution,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AutomationRule::class);

        return response()->json([
            'data' => AutomationRuleResource::collection($this->rules->paginate(
                $request->only(['search', 'module', 'status', 'trigger_type', 'trigger_event', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreAutomationRuleRequest $request): JsonResponse
    {
        $this->authorize('create', AutomationRule::class);
        $automationRule = $this->rules->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Automation rule created successfully.',
            'data' => new AutomationRuleResource($automationRule),
        ], 201);
    }

    public function update(UpdateAutomationRuleRequest $request, AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('update', $automationRule);
        $automationRule = $this->rules->update($automationRule, $request->validated());

        return response()->json([
            'message' => 'Automation rule updated successfully.',
            'data' => new AutomationRuleResource($automationRule),
        ]);
    }

    public function destroy(AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('delete', $automationRule);
        $this->rules->delete($automationRule);

        return response()->json(null, 204);
    }

    public function run(RunAutomationRuleRequest $request, AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('run', $automationRule);
        $automationRun = $this->execution->execute($automationRule, $request->validated(), $request->user());

        return response()->json([
            'message' => 'Automation rule executed successfully.',
            'data' => new AutomationRunResource($automationRun),
        ], 201);
    }

    public function activate(AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('activate', $automationRule);
        $automationRule = $this->rules->activate($automationRule);

        return response()->json([
            'message' => 'Automation rule activated successfully.',
            'data' => new AutomationRuleResource($automationRule),
        ]);
    }

    public function deactivate(AutomationRule $automationRule): JsonResponse
    {
        $this->authorize('deactivate', $automationRule);
        $automationRule = $this->rules->deactivate($automationRule);

        return response()->json([
            'message' => 'Automation rule deactivated successfully.',
            'data' => new AutomationRuleResource($automationRule),
        ]);
    }
}
