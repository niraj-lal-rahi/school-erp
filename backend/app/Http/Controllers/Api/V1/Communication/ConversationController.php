<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreConversationRequest;
use App\Http\Requests\Communication\StoreGroupMemberRequest;
use App\Models\Communication\CommunicationConversation;
use App\Services\Communication\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationService $conversations,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->conversations->paginate(
                $request->only(['search', 'status', 'recipient_type', 'recipient_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $conversation = $this->conversations->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Conversation created successfully.',
            'data' => $conversation,
        ], 201);
    }

    public function show(CommunicationConversation $conversation): JsonResponse
    {
        return response()->json([
            'data' => $this->conversations->findOrFail($conversation->id),
        ]);
    }

    public function update(Request $request, CommunicationConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'conversation_type' => ['sometimes', 'required', 'string', Rule::in(['direct', 'group', 'parent_teacher', 'staff', 'class_group'])],
            'title' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'archived', 'closed'])],
        ]);

        $conversation = $this->conversations->update($conversation, $validated);

        return response()->json([
            'message' => 'Conversation updated successfully.',
            'data' => $conversation,
        ]);
    }

    public function destroy(CommunicationConversation $conversation): JsonResponse
    {
        $this->conversations->delete($conversation);

        return response()->json(null, 204);
    }

    public function messages(CommunicationConversation $conversation): JsonResponse
    {
        return response()->json([
            'data' => $this->conversations->messages($conversation),
        ]);
    }

    public function addParticipant(StoreGroupMemberRequest $request, CommunicationConversation $conversation): JsonResponse
    {
        $participant = $this->conversations->addParticipant($conversation, $request->validated());

        return response()->json([
            'message' => 'Participant added successfully.',
            'data' => $participant,
        ], 201);
    }

    public function removeParticipant(CommunicationConversation $conversation, int $participantId): JsonResponse
    {
        $this->conversations->removeParticipant($conversation, $participantId);

        return response()->json(null, 204);
    }
}
