<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreScheduledMessageRequest;
use App\Models\Communication\ScheduledMessage;
use App\Services\Communication\ScheduledMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ScheduledMessageController extends Controller
{
    public function __construct(
        protected ScheduledMessageService $scheduledMessages,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->scheduledMessages->paginate(
                $request->only(['search', 'status', 'audience_type', 'channel', 'class_id', 'section_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreScheduledMessageRequest $request): JsonResponse
    {
        $scheduledMessage = $this->scheduledMessages->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Scheduled message created successfully.',
            'data' => $scheduledMessage,
        ], 201);
    }

    public function show(ScheduledMessage $scheduledMessage): JsonResponse
    {
        return response()->json([
            'data' => $this->scheduledMessages->findOrFail($scheduledMessage->id),
        ]);
    }

    public function update(Request $request, ScheduledMessage $scheduledMessage): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string'],
            'channel' => ['sometimes', 'required', 'string', Rule::in(['email', 'sms', 'push', 'in_app', 'multi'])],
            'scheduled_at' => ['sometimes', 'required', 'date', 'after:now'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['pending', 'processing', 'sent', 'failed', 'cancelled'])],
        ]);

        $scheduledMessage = $this->scheduledMessages->update($scheduledMessage, $validated);

        return response()->json([
            'message' => 'Scheduled message updated successfully.',
            'data' => $scheduledMessage,
        ]);
    }

    public function destroy(ScheduledMessage $scheduledMessage): JsonResponse
    {
        $this->scheduledMessages->delete($scheduledMessage);

        return response()->json(null, 204);
    }

    public function cancel(ScheduledMessage $scheduledMessage): JsonResponse
    {
        $scheduledMessage = $this->scheduledMessages->cancel($scheduledMessage);

        return response()->json([
            'message' => 'Scheduled message cancelled successfully.',
            'data' => $scheduledMessage,
        ]);
    }

    public function processDue(): JsonResponse
    {
        $processed = $this->scheduledMessages->processDueScheduledMessages();

        return response()->json([
            'message' => 'Due scheduled messages processed successfully.',
            'data' => $processed,
        ]);
    }
}
