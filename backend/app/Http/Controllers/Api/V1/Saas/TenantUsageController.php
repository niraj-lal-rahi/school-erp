<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantUsageResource;
use App\Services\Saas\TenantService;
use App\Services\Saas\TenantUsageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantUsageController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
        protected TenantUsageService $usage,
    ) {
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => new TenantUsageResource($this->usage->getUsage($tenant)),
        ]);
    }

    public function sync(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('update', $tenant);
        $usage = $this->usage->syncUsageCounters($tenant, null, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant usage synchronized successfully.',
            'data' => new TenantUsageResource($usage),
        ]);
    }
}
