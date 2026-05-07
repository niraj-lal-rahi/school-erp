<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Resources\FailedJobResource;
use App\Services\Queue\FailedJobMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformFailedJobController extends Controller
{
    public function __construct(
        protected FailedJobMonitoringService $failedJobs,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $limit = max(1, min(100, (int) $request->integer('limit', 50)));
        $recent = $this->failedJobs->recent($limit);
        $summary = $this->failedJobs->summary((int) $request->integer('hours', 24));

        PlatformAuditLog::query()->create([
            'tenant_id' => null,
            'user_id' => $request->user()?->id,
            'action' => 'platform_failed_jobs_viewed',
            'module' => 'platform_monitoring',
            'description' => 'Failed jobs list viewed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'limit' => $limit,
                'hours' => (int) $request->integer('hours', 24),
            ],
        ]);

        return response()->json([
            'data' => FailedJobResource::collection($recent),
            'summary' => $summary,
        ]);
    }
}
