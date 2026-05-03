<?php

namespace App\Http\Controllers\Api\V1\Workflows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflows\StoreReminderRuleRequest;
use App\Http\Requests\Workflows\UpdateReminderRuleRequest;
use App\Http\Resources\Workflows\ReminderRuleResource;
use App\Models\Workflows\ReminderRule;
use App\Services\Workflows\ReminderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReminderRuleController extends Controller
{
    public function __construct(
        protected ReminderService $reminders,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReminderRule::class);

        return response()->json([
            'data' => ReminderRuleResource::collection($this->reminders->paginate(
                $request->only(['search', 'module', 'status', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            )->getCollection()),
        ]);
    }

    public function store(StoreReminderRuleRequest $request): JsonResponse
    {
        $this->authorize('create', ReminderRule::class);
        $reminderRule = $this->reminders->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Reminder rule created successfully.',
            'data' => new ReminderRuleResource($reminderRule),
        ], 201);
    }

    public function update(UpdateReminderRuleRequest $request, ReminderRule $reminderRule): JsonResponse
    {
        $this->authorize('update', $reminderRule);
        $reminderRule = $this->reminders->update($reminderRule, $request->validated());

        return response()->json([
            'message' => 'Reminder rule updated successfully.',
            'data' => new ReminderRuleResource($reminderRule),
        ]);
    }

    public function destroy(ReminderRule $reminderRule): JsonResponse
    {
        $this->authorize('delete', $reminderRule);
        $this->reminders->delete($reminderRule);

        return response()->json(null, 204);
    }

    public function processDue(Request $request): JsonResponse
    {
        $this->authorize('process', ReminderRule::class);
        $result = $this->reminders->processDue($request->only(['targets']));

        return response()->json([
            'message' => 'Due reminders processed successfully.',
            'data' => $result,
        ]);
    }
}
