<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\UpdateSecuritySettingRequest;
use App\Http\Resources\Settings\SecuritySettingResource;
use App\Models\Settings\SecuritySetting;
use App\Services\Settings\SecuritySettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SecuritySettingController extends BaseSettingController
{
    public function __construct(
        protected SecuritySettingService $security,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $setting = $this->security->getForTenant($this->scopedSchoolId($request, true));

        if ($setting !== null) {
            $this->authorize('view', $setting);
        } else {
            $this->authorize('updateScope', [SecuritySetting::class, $this->scopedSchoolId($request, true)]);
        }

        return response()->json([
            'data' => $setting ? new SecuritySettingResource($setting) : null,
        ]);
    }

    public function update(UpdateSecuritySettingRequest $request): JsonResponse
    {
        $schoolId = $this->scopedSchoolId($request, true);
        $existing = $this->security->getForTenant($schoolId);

        if ($existing !== null) {
            $this->authorize('update', $existing);
        } else {
            $this->authorize('updateScope', [SecuritySetting::class, $schoolId]);
        }

        $security = $this->security->update(
            $schoolId,
            $request->validated(),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Security settings updated successfully.',
            'data' => new SecuritySettingResource($security),
        ]);
    }
}
