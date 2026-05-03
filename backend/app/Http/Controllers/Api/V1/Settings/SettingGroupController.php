<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\StoreSettingGroupRequest;
use App\Http\Requests\Settings\UpdateSettingGroupRequest;
use App\Http\Resources\Settings\SettingGroupResource;
use App\Models\Settings\SettingGroup;
use App\Services\Settings\SettingGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingGroupController extends BaseSettingController
{
    public function __construct(
        protected SettingGroupService $groups,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SettingGroup::class);

        return response()->json([
            'data' => SettingGroupResource::collection($this->groups->list($request->only(['scope', 'status']))),
        ]);
    }

    public function store(StoreSettingGroupRequest $request): JsonResponse
    {
        $this->authorize('create', [SettingGroup::class, $request->validated()]);

        $group = $this->groups->create($this->scopedAttributes($request, $request->validated(), true));

        return response()->json([
            'message' => 'Setting group created successfully.',
            'data' => new SettingGroupResource($group),
        ], 201);
    }

    public function update(UpdateSettingGroupRequest $request, int $id): JsonResponse
    {
        $group = $this->groups->findOrFail($id);
        $this->authorize('update', $group);
        $group = $this->groups->update($group, $this->scopedAttributes($request, $request->validated(), $group->school_id === null));

        return response()->json([
            'message' => 'Setting group updated successfully.',
            'data' => new SettingGroupResource($group),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $group = $this->groups->findOrFail($id);
        $this->authorize('delete', $group);
        $this->groups->delete($group);

        return response()->json(null, 204);
    }
}
