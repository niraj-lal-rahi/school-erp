<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\UpdateFeatureFlagRequest;
use App\Http\Resources\Settings\FeatureFlagResource;
use App\Models\Settings\FeatureFlag;
use App\Services\Settings\FeatureFlagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeatureFlagController extends BaseSettingController
{
    public function __construct(
        protected FeatureFlagService $features,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeatureFlag::class);

        return response()->json([
            'data' => FeatureFlagResource::collection($this->features->list($request->only(['module', 'is_enabled']))),
        ]);
    }

    public function update(UpdateFeatureFlagRequest $request, int $id): JsonResponse
    {
        $feature = $this->features->findOrFail($id);
        $this->authorize('update', $feature);
        $feature = $this->features->update(
            $feature,
            $this->scopedAttributes($request, $request->validated(), $feature->school_id === null),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Feature flag updated successfully.',
            'data' => new FeatureFlagResource($feature),
        ]);
    }

    public function enable(Request $request, int $id): JsonResponse
    {
        $feature = $this->features->findOrFail($id);
        $this->authorize('enable', $feature);
        $feature = $this->features->enable($feature, $request->user(), $request);

        return response()->json([
            'message' => 'Feature flag enabled successfully.',
            'data' => new FeatureFlagResource($feature),
        ]);
    }

    public function disable(Request $request, int $id): JsonResponse
    {
        $feature = $this->features->findOrFail($id);
        $this->authorize('disable', $feature);
        $feature = $this->features->disable($feature, $request->user(), $request);

        return response()->json([
            'message' => 'Feature flag disabled successfully.',
            'data' => new FeatureFlagResource($feature),
        ]);
    }
}
