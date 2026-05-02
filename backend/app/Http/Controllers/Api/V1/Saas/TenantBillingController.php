<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantBillingResource;
use App\Models\Saas\TenantBillingRecord;
use App\Services\Saas\TenantBillingService;
use App\Services\Saas\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantBillingController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
        protected TenantBillingService $billing,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => TenantBillingResource::collection($this->billing->listByTenant($tenant)),
        ]);
    }

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $record = $this->billing->findOrFail($id);
        $this->authorize('update', $record);
        $record = $this->billing->markPaid($record, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Billing record marked paid successfully.',
            'data' => new TenantBillingResource($record),
        ]);
    }

    public function markFailed(Request $request, int $id): JsonResponse
    {
        $record = $this->billing->findOrFail($id);
        $this->authorize('update', $record);
        $record = $this->billing->markFailed($record, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Billing record marked failed successfully.',
            'data' => new TenantBillingResource($record),
        ]);
    }
}
