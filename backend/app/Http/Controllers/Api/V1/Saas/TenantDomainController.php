<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantDomainResource;
use App\Http\Requests\Saas\StoreTenantDomainRequest;
use App\Services\Saas\TenantDomainService;
use App\Services\Saas\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantDomainController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
        protected TenantDomainService $domains,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json([
            'data' => TenantDomainResource::collection($this->domains->listByTenant($tenant)),
        ]);
    }

    public function store(StoreTenantDomainRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('update', $tenant);

        $domain = $this->domains->createDomain($tenant, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant domain created successfully.',
            'data' => new TenantDomainResource($domain),
        ], 201);
    }

    public function verify(Request $request, int $id): JsonResponse
    {
        $domain = $this->domains->findOrFail($id);
        $this->authorize('update', $domain->tenant()->firstOrFail());
        $domain = $this->domains->verifyDomain($domain, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant domain verified successfully.',
            'data' => new TenantDomainResource($domain),
        ]);
    }
}
