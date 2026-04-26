<?php

namespace App\Http\Controllers\Api\V1\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\StoreCommunicationGroupRequest;
use App\Http\Requests\Communication\StoreGroupMemberRequest;
use App\Http\Requests\Communication\UpdateCommunicationGroupRequest;
use App\Models\Communication\CommunicationGroup;
use App\Services\Communication\CommunicationGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunicationGroupController extends Controller
{
    public function __construct(
        protected CommunicationGroupService $groups,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->groups->paginate(
                $request->only(['search', 'status', 'class_id', 'section_id', 'recipient_type', 'recipient_id']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreCommunicationGroupRequest $request): JsonResponse
    {
        $group = $this->groups->create([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Communication group created successfully.',
            'data' => $group,
        ], 201);
    }

    public function show(CommunicationGroup $group): JsonResponse
    {
        return response()->json([
            'data' => $this->groups->findOrFail($group->id),
        ]);
    }

    public function update(UpdateCommunicationGroupRequest $request, CommunicationGroup $group): JsonResponse
    {
        $group = $this->groups->update($group, $request->validated());

        return response()->json([
            'message' => 'Communication group updated successfully.',
            'data' => $group,
        ]);
    }

    public function destroy(CommunicationGroup $group): JsonResponse
    {
        $this->groups->delete($group);

        return response()->json(null, 204);
    }

    public function addMember(StoreGroupMemberRequest $request, CommunicationGroup $group): JsonResponse
    {
        $member = $this->groups->addMember($group, $request->validated());

        return response()->json([
            'message' => 'Group member added successfully.',
            'data' => $member,
        ], 201);
    }

    public function removeMember(CommunicationGroup $group, int $memberId): JsonResponse
    {
        $this->groups->removeMember($group, $memberId);

        return response()->json(null, 204);
    }
}
