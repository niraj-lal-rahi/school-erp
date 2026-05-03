<?php

namespace App\Http\Controllers\Api\V1\Settings;

use App\Http\Resources\Settings\SettingAuditLogResource;
use App\Models\Settings\SettingAuditLog;
use App\Services\Settings\SettingAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingAuditLogController extends BaseSettingController
{
    public function __construct(
        protected SettingAuditService $audits,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SettingAuditLog::class);

        return response()->json([
            'data' => SettingAuditLogResource::collection($this->audits->list($request->only(['setting_type', 'setting_key']))),
        ]);
    }
}
