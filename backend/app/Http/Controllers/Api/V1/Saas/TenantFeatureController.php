<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\PlanFeatureResource;
use App\Http\Requests\Saas\UpdateTenantFeatureAccessRequest;
use App\Models\Saas\Tenant;
use App\Services\Saas\TenantFeatureService;
use App\Services\Saas\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantFeatureController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
        protected TenantFeatureService $features,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => PlanFeatureResource::collection($this->features->listFeatures($tenant, $request->only(['module']))),
        ]);
    }

    public function update(UpdateTenantFeatureAccessRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('update', $tenant);

        foreach ($request->validated('features') as $featureData) {
            if (($featureData['is_enabled'] ?? true) === false) {
                $this->features->disableFeature($tenant, $featureData, $request->user(), $request->ip());
            } else {
                $this->features->enableFeature($tenant, $featureData, $request->user(), $request->ip());
            }
        }

        return response()->json([
            'message' => 'Tenant feature access updated successfully.',
            'data' => PlanFeatureResource::collection($this->features->listFeatures($tenant)),
        ]);
    }
}
