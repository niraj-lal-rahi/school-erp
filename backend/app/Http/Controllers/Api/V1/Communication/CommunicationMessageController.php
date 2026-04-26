<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationMessageRequest;
use App\Models\Communication\CommunicationMessage;
use App\Services\Communication\CommunicationMessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationMessageController extends Controller
{
    public function __construct(
        protected CommunicationMessageService $messages,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->messages->paginate(
                $request->only(['search', 'status', 'priority', 'recipient_type', 'recipient_id', 'channel', 'conversation_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreCommunicationMessageRequest $request): JsonResponse
    {
        $payload = [
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'sender_type' => 'user',
            'sender_id' => $request->user()->id,
            'sender_user_id' => $request->user()->id,
        ];

        $message = ($payload['recipient_type'] ?? null) === 'group'
            ? $this->messages->sendGroupMessage($payload)
            : $this->messages->sendDirectMessage($payload);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $message,
        ], 201);
    }

    public function show(CommunicationMessage $message): JsonResponse
    {
        return response()->json([
            'data' => $this->messages->findOrFail($message->id),
        ]);
    }

    public function destroy(CommunicationMessage $message): JsonResponse
    {
        $this->messages->delete($message);

        return response()->json(null, 204);
    }

    public function markRead(CommunicationMessage $message): JsonResponse
    {
        $message = $this->messages->markAsRead($message);

        return response()->json([
            'message' => 'Message marked as read successfully.',
            'data' => $message,
        ]);
    }

    public function archive(CommunicationMessage $message): JsonResponse
    {
        $message = $this->messages->archive($message);

        return response()->json([
            'message' => 'Message archived successfully.',
            'data' => $message,
        ]);
    }
}
