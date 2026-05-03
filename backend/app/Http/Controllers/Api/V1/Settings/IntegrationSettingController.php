<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\UpdateIntegrationSettingRequest;
use App\Http\Resources\Settings\IntegrationSettingResource;
use App\Models\Settings\IntegrationSetting;
use App\Services\Settings\IntegrationSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationSettingController extends BaseSettingController
{
    public function __construct(
        protected IntegrationSettingService $integrations,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', IntegrationSetting::class);

        return response()->json([
            'data' => IntegrationSettingResource::collection($this->integrations->list($request->only(['integration_type', 'provider', 'status']))),
        ]);
    }

    public function store(UpdateIntegrationSettingRequest $request): JsonResponse
    {
        $this->authorize('create', [IntegrationSetting::class, $request->validated()]);

        $integration = $this->integrations->create(
            $this->scopedAttributes($request, $request->validated(), true),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Integration setting created successfully.',
            'data' => new IntegrationSettingResource($integration),
        ], 201);
    }

    public function update(UpdateIntegrationSettingRequest $request, int $id): JsonResponse
    {
        $integration = $this->integrations->findOrFail($id);
        $this->authorize('update', $integration);
        $integration = $this->integrations->update(
            $integration,
            $this->scopedAttributes($request, $request->validated(), $integration->school_id === null),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Integration setting updated successfully.',
            'data' => new IntegrationSettingResource($integration),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $integration = $this->integrations->findOrFail($id);
        $this->authorize('delete', $integration);
        $this->integrations->delete($integration, $request->user(), $request);

        return response()->json(null, 204);
    }
}
