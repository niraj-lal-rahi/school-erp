<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\StoreSettingRequest;
use App\Http\Requests\Settings\UpdateSettingRequest;
use App\Http\Resources\Settings\SettingResource;
use App\Models\Settings\Setting;
use App\Services\Settings\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends BaseSettingController
{
    public function __construct(
        protected SettingService $settings,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        return response()->json([
            'data' => SettingResource::collection($this->settings->list($request->only(['group_id', 'scope', 'is_public', 'is_sensitive']))),
        ]);
    }

    public function store(StoreSettingRequest $request): JsonResponse
    {
        $this->authorize('create', [Setting::class, $request->validated()]);

        $setting = $this->settings->create(
            $this->scopedAttributes($request, $request->validated(), ($request->validated()['scope'] ?? 'tenant') === 'global'),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Setting created successfully.',
            'data' => new SettingResource($setting),
        ], 201);
    }

    public function update(UpdateSettingRequest $request, int $id): JsonResponse
    {
        $setting = $this->settings->findModelOrFail($id);
        $this->authorize('update', $setting);
        $allowGlobal = $setting->scope === 'global' && $setting->school_id === null;

        $setting = $this->settings->update(
            $setting,
            $this->scopedAttributes($request, $request->validated(), $allowGlobal),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Setting updated successfully.',
            'data' => new SettingResource($setting),
        ]);
    }

    public function showByKey(Request $request, string $key): JsonResponse
    {
        return response()->json([
            'data' => [
                'key' => $key,
                'value' => $this->settings->getByKey($key, $this->scopedSchoolId($request, true)),
            ],
        ]);
    }
}
