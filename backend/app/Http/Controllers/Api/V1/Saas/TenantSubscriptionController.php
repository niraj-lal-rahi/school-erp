<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantSubscriptionResource;
use App\Http\Requests\Saas\ChangeTenantPlanRequest;
use App\Http\Requests\Saas\SubscribeTenantRequest;
use App\Models\Saas\TenantSubscription;
use App\Services\Saas\SubscriptionPlanService;
use App\Services\Saas\TenantService;
use App\Services\Saas\TenantSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSubscriptionController extends SaasController
{
    public function __construct(
        protected TenantService $tenants,
        protected SubscriptionPlanService $plans,
        protected TenantSubscriptionService $subscriptions,
    ) {
    }

    public function subscribe(SubscribeTenantRequest $request, int $id): JsonResponse
    {
        $this->authorize('create', TenantSubscription::class);
        $tenant = $this->tenants->findOrFail($id);
        $plan = $this->plans->findOrFail((int) $request->validated('subscription_plan_id'));

        $subscription = $this->subscriptions->subscribeTenant($tenant, $plan, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant subscribed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ], 201);
    }

    public function changePlan(ChangeTenantPlanRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $currentSubscription = $this->subscriptions->findActiveForTenant($tenant);
        if ($currentSubscription) {
            $this->authorize('changePlan', $currentSubscription);
        } else {
            $this->authorize('create', TenantSubscription::class);
        }
        $plan = $this->plans->findOrFail((int) $request->validated('subscription_plan_id'));

        $subscription = $this->subscriptions->changePlan($tenant, $plan, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant plan changed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }

    public function renew(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $subscription = $this->subscriptions->findActiveForTenant($tenant);
        abort_if(! $subscription, 404, 'Active subscription not found for this tenant.');
        $this->authorize('update', $subscription);

        $payload = $request->validate([
            'end_date' => ['nullable', 'date'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $subscription = $this->subscriptions->renewSubscription($subscription, $payload, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant subscription renewed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $tenant = $this->tenants->findOrFail($id);
        $subscription = $this->subscriptions->findActiveForTenant($tenant);
        abort_if(! $subscription, 404, 'Active subscription not found for this tenant.');
        $this->authorize('update', $subscription);
        $subscription = $this->subscriptions->cancelSubscription($subscription, $request->user(), $request->ip());

        return response()->json([
            'message' => 'Tenant subscription cancelled successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }
}
