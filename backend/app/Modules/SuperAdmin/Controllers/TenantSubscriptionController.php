<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Models\SubscriptionPlan;
use App\Modules\SuperAdmin\Resources\TenantSubscriptionResource;
use App\Modules\SuperAdmin\Services\TenantSubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSubscriptionController extends Controller
{
    public function __construct(
        protected TenantSubscriptionService $subscriptions,
    ) {
    }

    public function subscribe(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'subscription_plan_id' => ['required', 'integer', 'exists:platform.subscription_plans,id'],
            'billing_cycle' => ['required', 'in:monthly,yearly'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:trial,active,past_due,suspended,cancelled,expired'],
            'auto_renew' => ['nullable', 'boolean'],
            'next_billing_at' => ['nullable', 'date'],
        ]);

        $tenant = PlatformTenant::query()->findOrFail($id);
        $plan = SubscriptionPlan::query()->findOrFail((int) $payload['subscription_plan_id']);

        $subscription = $this->subscriptions->subscribeTenant(
            $tenant,
            $plan,
            $payload,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant subscribed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ], 201);
    }

    public function changePlan(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'subscription_plan_id' => ['required', 'integer', 'exists:platform.subscription_plans,id'],
            'billing_cycle' => ['nullable', 'in:monthly,yearly'],
            'end_date' => ['nullable', 'date'],
            'trial_ends_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:trial,active,past_due,suspended,cancelled,expired'],
            'auto_renew' => ['nullable', 'boolean'],
            'next_billing_at' => ['nullable', 'date'],
        ]);

        $tenant = PlatformTenant::query()->findOrFail($id);
        $plan = SubscriptionPlan::query()->findOrFail((int) $payload['subscription_plan_id']);

        $subscription = $this->subscriptions->changePlan(
            $tenant,
            $plan,
            $payload,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant plan changed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }

    public function renew(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'end_date' => ['nullable', 'date'],
            'auto_renew' => ['nullable', 'boolean'],
        ]);

        $tenant = PlatformTenant::query()->findOrFail($id);
        $subscription = $this->subscriptions->findActiveForTenant($tenant);
        abort_if(! $subscription, 404, 'Active subscription not found for this tenant.');

        $subscription = $this->subscriptions->renewSubscription(
            $subscription,
            $payload,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant subscription renewed successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);
        $subscription = $this->subscriptions->findActiveForTenant($tenant);
        abort_if(! $subscription, 404, 'Active subscription not found for this tenant.');

        $subscription = $this->subscriptions->cancelSubscription(
            $subscription,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant subscription cancelled successfully.',
            'data' => new TenantSubscriptionResource($subscription),
        ]);
    }
}
