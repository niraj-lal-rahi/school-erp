<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\TenantResource;
use App\Http\Requests\Saas\StoreTenantRequest;
use App\Models\Saas\Tenant;
use App\Services\Saas\SubscriptionPlanService;
use App\Services\Saas\TenantOnboardingService;
use Illuminate\Http\JsonResponse;

class TenantOnboardingController extends SaasController
{
    public function __construct(
        protected TenantOnboardingService $onboarding,
        protected SubscriptionPlanService $plans,
    ) {
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $this->authorize('create', Tenant::class);

        $payload = $request->validate([
            'tenant' => ['nullable', 'array'],
            'admin' => ['required', 'array'],
            'admin.first_name' => ['required', 'string', 'max:255'],
            'admin.last_name' => ['required', 'string', 'max:255'],
            'admin.email' => ['required', 'email', 'max:255'],
            'admin.phone' => ['nullable', 'string', 'max:30'],
            'admin.password' => ['required', 'string', 'min:8'],
            'plan_id' => ['nullable', 'integer', 'exists:subscription_plans,id'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $tenantAttributes = $payload['tenant'] ?? $request->validated();
        $plan = ! empty($payload['plan_id']) ? $this->plans->findOrFail((int) $payload['plan_id']) : null;

        $result = $this->onboarding->onboardSchool(
            $tenantAttributes,
            $payload['admin'],
            $plan,
            (int) ($payload['trial_days'] ?? 14),
            $request->user(),
            $request->ip(),
        );

        return response()->json([
            'message' => 'Tenant onboarding completed successfully.',
            'data' => [
                'tenant' => new TenantResource($result['tenant']),
                'admin_user' => [
                    'id' => $result['admin_user']->id,
                    'name' => $result['admin_user']->name,
                    'email' => $result['admin_user']->email,
                    'school_id' => $result['admin_user']->school_id,
                ],
            ],
        ], 201);
    }
}
