<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Resources\Settings\PublicConfigResource;
use App\Services\Settings\PublicConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicConfigController extends BaseSettingController
{
    public function __construct(
        protected PublicConfigService $config,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new PublicConfigResource($this->config->get($this->currentSchoolId($request))),
        ]);
    }
}
