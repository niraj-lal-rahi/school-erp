<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationChannelRequest;
use App\Http\Requests\Communication\UpdateCommunicationChannelRequest;
use App\Models\Communication\CommunicationChannel;
use App\Services\Communication\CommunicationChannelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationChannelController extends Controller
{
    public function __construct(
        protected CommunicationChannelService $channels,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->channels->paginate(
                $request->only(['search', 'status', 'channel_type']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreCommunicationChannelRequest $request): JsonResponse
    {
        $channel = $this->channels->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Communication channel created successfully.',
            'data' => $channel,
        ], 201);
    }

    public function show(CommunicationChannel $channel): JsonResponse
    {
        return response()->json([
            'data' => $this->channels->findOrFail($channel->id),
        ]);
    }

    public function update(UpdateCommunicationChannelRequest $request, CommunicationChannel $channel): JsonResponse
    {
        $channel = $this->channels->update($channel, $request->validated());

        return response()->json([
            'message' => 'Communication channel updated successfully.',
            'data' => $channel,
        ]);
    }

    public function destroy(CommunicationChannel $channel): JsonResponse
    {
        $this->channels->delete($channel);

        return response()->json(null, 204);
    }
}
