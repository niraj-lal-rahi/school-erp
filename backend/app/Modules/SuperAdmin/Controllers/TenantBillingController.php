<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Resources\TenantBillingResource;
use App\Modules\SuperAdmin\Services\TenantBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantBillingController extends Controller
{
    public function __construct(
        protected TenantBillingService $billing,
    ) {
    }

    public function index(int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);

        return response()->json([
            'data' => TenantBillingResource::collection($this->billing->listByTenant($tenant)),
        ]);
    }

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $record = $this->billing->markPaid(
            $this->billing->findOrFail($id),
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Billing record marked paid successfully.',
            'data' => new TenantBillingResource($record),
        ]);
    }

    public function markFailed(Request $request, int $id): JsonResponse
    {
        $record = $this->billing->markFailed(
            $this->billing->findOrFail($id),
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Billing record marked failed successfully.',
            'data' => new TenantBillingResource($record),
        ]);
    }
}
