<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Requests\StorePlatformTenantRequest;
use App\Modules\SuperAdmin\Requests\UpdatePlatformTenantRequest;
use App\Modules\SuperAdmin\Resources\PlatformTenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformTenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = PlatformTenant::query()
            ->with(['activeDatabaseConnection', 'securitySetting', 'subscriptions'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim($request->string('search')->toString());

                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%')
                        ->orWhere('slug', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 15));

        $this->logAction($request, 'platform_tenant_listed', 'platform_tenant', 'Platform tenants listed.', [
            'filters' => $request->only(['search', 'status', 'per_page']),
        ]);

        return response()->json([
            'data' => PlatformTenantResource::collection($tenants->getCollection()),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ],
        ]);
    }

    public function store(StorePlatformTenantRequest $request): JsonResponse
    {
        $tenant = PlatformTenant::query()->create([
            'name' => $request->validated('name'),
            'code' => $request->validated('code'),
            'slug' => $request->validated('slug') ?: Str::slug((string) $request->validated('name')),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'status' => $request->validated('status', 'trial'),
            'trial_ends_at' => $request->validated('trial_ends_at'),
            'activated_at' => $request->validated('activated_at'),
            'suspended_at' => $request->validated('suspended_at'),
        ]);

        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_created', 'platform_tenant', 'Platform tenant created.', [
            'tenant_id' => $tenant->id,
            'tenant_code' => $tenant->code,
        ], $tenant->id);

        return response()->json([
            'message' => 'Platform tenant created successfully.',
            'data' => new PlatformTenantResource($tenant),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $tenant = $this->findTenant($id);
        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_viewed', 'platform_tenant', 'Platform tenant viewed.', [
            'tenant_id' => $tenant->id,
        ], $tenant->id);

        return response()->json([
            'data' => new PlatformTenantResource($tenant),
        ]);
    }

    public function update(UpdatePlatformTenantRequest $request, int $id): JsonResponse
    {
        $tenant = $this->findTenant($id);
        $tenant->fill($request->validated());
        $tenant->save();
        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_updated', 'platform_tenant', 'Platform tenant updated.', [
            'tenant_id' => $tenant->id,
            'changes' => $request->validated(),
        ], $tenant->id);

        return response()->json([
            'message' => 'Platform tenant updated successfully.',
            'data' => new PlatformTenantResource($tenant),
        ]);
    }

    public function activate(Request $request, int $id): JsonResponse
    {
        $tenant = $this->findTenant($id);
        $tenant->update([
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
        ]);

        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_activated', 'platform_tenant', 'Platform tenant activated.', [
            'tenant_id' => $tenant->id,
        ], $tenant->id);

        return response()->json([
            'message' => 'Platform tenant activated successfully.',
            'data' => new PlatformTenantResource($tenant),
        ]);
    }

    public function suspend(Request $request, int $id): JsonResponse
    {
        $tenant = $this->findTenant($id);
        $tenant->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);

        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_suspended', 'platform_tenant', 'Platform tenant suspended.', [
            'tenant_id' => $tenant->id,
        ], $tenant->id);

        return response()->json([
            'message' => 'Platform tenant suspended successfully.',
            'data' => new PlatformTenantResource($tenant),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $tenant = $this->findTenant($id);
        $tenant->update([
            'status' => 'cancelled',
        ]);

        $tenant->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_cancelled', 'platform_tenant', 'Platform tenant cancelled.', [
            'tenant_id' => $tenant->id,
        ], $tenant->id);

        return response()->json([
            'message' => 'Platform tenant cancelled successfully.',
            'data' => new PlatformTenantResource($tenant),
        ]);
    }

    protected function findTenant(int $id): PlatformTenant
    {
        return PlatformTenant::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function logAction(
        Request $request,
        string $action,
        string $module,
        string $description,
        array $metadata = [],
        ?int $tenantId = null,
    ): void {
        PlatformAuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => $request->user()?->id,
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
