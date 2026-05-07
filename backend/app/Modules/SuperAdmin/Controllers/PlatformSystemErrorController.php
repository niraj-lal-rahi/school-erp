<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Resources\SystemErrorResource;
use App\Modules\SuperAdmin\Services\SystemErrorLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformSystemErrorController extends Controller
{
    public function __construct(
        protected SystemErrorLogService $errors,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $limit = max(1, min(100, (int) $request->integer('limit', 50)));
        $errors = $this->errors->recent($limit);

        PlatformAuditLog::query()->create([
            'tenant_id' => null,
            'user_id' => $request->user()?->id,
            'action' => 'platform_system_errors_viewed',
            'module' => 'platform_monitoring',
            'description' => 'System error logs viewed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'limit' => $limit,
            ],
        ]);

        return response()->json([
            'data' => SystemErrorResource::collection($errors),
        ]);
    }
}
