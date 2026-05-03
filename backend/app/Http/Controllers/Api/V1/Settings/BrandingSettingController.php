<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\UpdateBrandingSettingRequest;
use App\Http\Resources\Settings\BrandingSettingResource;
use App\Models\Settings\BrandingSetting;
use App\Services\Settings\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandingSettingController extends BaseSettingController
{
    public function __construct(
        protected BrandingService $branding,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $existing = $this->branding->getForTenant($this->currentSchoolId($request));

        if ($existing !== null) {
            $this->authorize('view', $existing);
        } else {
            $this->authorize('updateTenant', [BrandingSetting::class, $this->currentSchoolId($request)]);
        }

        return response()->json([
            'data' => $existing ? new BrandingSettingResource($existing) : null,
        ]);
    }

    public function update(UpdateBrandingSettingRequest $request): JsonResponse
    {
        $existing = $this->branding->getForTenant($this->currentSchoolId($request));

        if ($existing !== null) {
            $this->authorize('update', $existing);
        } else {
            $this->authorize('updateTenant', [BrandingSetting::class, $this->currentSchoolId($request)]);
        }

        $branding = $this->branding->update(
            $this->currentSchoolId($request),
            $request->validated(),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Branding settings updated successfully.',
            'data' => new BrandingSettingResource($branding),
        ]);
    }
}
