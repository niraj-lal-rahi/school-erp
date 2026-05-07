<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformAuditLog;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Requests\ProvisionTenantDatabaseRequest;
use App\Modules\SuperAdmin\Requests\ProvisionTenantRequest;
use App\Modules\SuperAdmin\Resources\PlatformTenantResource;
use App\Modules\SuperAdmin\Resources\TenantDatabaseConnectionResource;
use App\Modules\SuperAdmin\Services\TenantDatabaseProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantProvisioningController extends Controller
{
    public function __construct(
        protected TenantDatabaseProvisioningService $provisioning,
    ) {
    }

    public function store(ProvisionTenantRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $plan = null;

        if (! empty($payload['plan_id'])) {
            $plan = SubscriptionPlan::query()->findOrFail((int) $payload['plan_id']);
        }

        $result = $this->provisioning->provision(
            $payload['tenant'],
            $payload['admin'],
            $plan,
            (int) ($payload['trial_days'] ?? 14),
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant provisioned successfully.',
            'data' => [
                'tenant' => [
                    'id' => $result['platform_tenant']->id,
                    'name' => $result['platform_tenant']->name,
                    'code' => $result['platform_tenant']->code,
                    'slug' => $result['platform_tenant']->slug,
                    'status' => $result['platform_tenant']->status,
                    'trial_ends_at' => optional($result['platform_tenant']->trial_ends_at)?->toISOString(),
                ],
                'database_connection' => [
                    'id' => $result['database_connection']->id,
                    'connection_name' => $result['database_connection']->connection_name,
                    'database_name' => $result['database_connection']->database_name,
                    'connection_status' => $result['database_connection']->connection_status,
                ],
                'subscription' => [
                    'id' => $result['subscription']->id,
                    'subscription_plan_id' => $result['subscription']->subscription_plan_id,
                    'status' => $result['subscription']->status,
                    'trial_ends_at' => optional($result['subscription']->trial_ends_at)?->toISOString(),
                ],
                'tenant_admin' => [
                    'user_id' => $result['local_admin_user_id'],
                    'role_id' => $result['tenant_admin_role_id'],
                    'school_id' => $result['local_school_id'],
                ],
            ],
        ], 201);
    }

    public function provisionDatabase(ProvisionTenantDatabaseRequest $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);
        $payload = $request->validated();

        $plan = null;

        if (! empty($payload['plan_id'])) {
            $plan = SubscriptionPlan::query()->findOrFail((int) $payload['plan_id']);
        }

        $result = $this->provisioning->provisionDatabaseForTenant(
            $tenant,
            $payload['admin'],
            $payload['tenant_profile'] ?? [],
            $plan,
            (int) ($payload['trial_days'] ?? 14),
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        $tenant->refresh()->load(['activeDatabaseConnection', 'securitySetting', 'subscriptions']);

        $this->logAction($request, 'platform_tenant_database_provisioned', 'tenant_database', 'Tenant database provisioned.', [
            'tenant_id' => $tenant->id,
            'database_name' => $result['database_connection']->database_name,
            'local_school_id' => $result['local_school_id'],
            'local_admin_user_id' => $result['local_admin_user_id'],
        ], $tenant->id);

        return response()->json([
            'message' => 'Tenant database provisioned successfully.',
            'data' => [
                'tenant' => new PlatformTenantResource($tenant),
                'database_connection' => new TenantDatabaseConnectionResource($result['database_connection']),
                'subscription' => [
                    'id' => $result['subscription']->id,
                    'subscription_plan_id' => $result['subscription']->subscription_plan_id,
                    'status' => $result['subscription']->status,
                ],
                'tenant_admin' => [
                    'user_id' => $result['local_admin_user_id'],
                    'role_id' => $result['tenant_admin_role_id'],
                    'school_id' => $result['local_school_id'],
                ],
            ],
        ], 201);
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
