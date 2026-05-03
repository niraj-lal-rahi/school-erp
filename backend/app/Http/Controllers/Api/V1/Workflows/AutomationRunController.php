<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Resources\Workflows\AutomationRunResource;
use App\Models\Workflows\AutomationRun;
use App\Services\Workflows\AutomationExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationRunController extends Controller
{
    public function __construct(
        protected AutomationExecutionService $execution,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AutomationRun::class);

        return response()->json([
            'data' => AutomationRunResource::collection($this->execution->paginateRuns(
                $request->only(['module', 'status', 'trigger_type', 'trigger_event', 'automation_rule_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }
}
