<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Requests\Settings\UpdateLocalizationSettingRequest;
use App\Http\Resources\Settings\LocalizationSettingResource;
use App\Models\Settings\LocalizationSetting;
use App\Services\Settings\LocalizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocalizationSettingController extends BaseSettingController
{
    public function __construct(
        protected LocalizationService $localization,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('viewAny', LocalizationSetting::class);

        return response()->json([
            'data' => ($setting = $this->localization->getForTenant($this->currentSchoolId($request)))
                ? new LocalizationSettingResource($setting)
                : null,
        ]);
    }

    public function update(UpdateLocalizationSettingRequest $request): JsonResponse
    {
        $this->authorize('create', [LocalizationSetting::class, ['school_id' => $this->currentSchoolId($request)]]);

        $localization = $this->localization->update(
            $this->currentSchoolId($request),
            $request->validated(),
            $request->user(),
            $request,
        );

        return response()->json([
            'message' => 'Localization settings updated successfully.',
            'data' => new LocalizationSettingResource($localization),
        ]);
    }
}
