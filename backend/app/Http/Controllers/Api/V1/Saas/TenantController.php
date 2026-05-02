<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantResource;
use App\Models\Saas\Tenant;
use App\Http\Requests\Saas\StoreTenantRequest;
use App\Http\Requests\Saas\UpdateTenantRequest;
use App\Services\Saas\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tenant::class);

        if (! app(\App\Services\Rbac\AccessControlService::class)->isSuperAdmin($request->user())) {
            $tenant = $this->tenants->findOrFail((int) $request->user()->school_id);

            return response()->json([
                'data' => [new TenantResource($tenant)],
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 1,
                    'total' => 1,
                ],
            ]);
        }

        $tenants = $this->tenants->paginate(
            $request->only(['search', 'status', 'plan', 'trial_expiring']),
            (int) $request->integer('per_page', 15),
        );

        return response()->json([
            'data' => TenantResource::collection($tenants->getCollection()),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
        ]);
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $this->authorize('create', Tenant::class);

        $tenant = $this->tenants->createTenant($request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant created successfully.',
            'data' => new TenantResource($tenant),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('view', $tenant);

        return response()->json(['data' => new TenantResource($tenant)]);
    }

    public function update(UpdateTenantRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('update', $tenant);

        $tenant = $this->tenants->updateTenant($tenant, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant updated successfully.',
            'data' => new TenantResource($tenant),
        ]);
    }

    public function activate(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('activate', $tenant);
        $tenant = $this->tenants->activateTenant($tenant, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant activated successfully.',
            'data' => new TenantResource($tenant),
        ]);
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('suspend', $tenant);
        $tenant = $this->tenants->suspendTenant($tenant, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant suspended successfully.',
            'data' => new TenantResource($tenant),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $this->authorize('cancel', $tenant);
        $tenant = $this->tenants->cancelTenant($tenant, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant cancelled successfully.',
            'data' => new TenantResource($tenant),
        ]);
    }
}
