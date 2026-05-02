<?php

namespace App\Http\Controllers\Api\V1\Saas;

use App\Http\Resources\Saas\SubscriptionPlanResource;
use App\Http\Requests\Saas\StorePlanFeatureRequest;
use App\Http\Requests\Saas\StoreSubscriptionPlanRequest;
use App\Http\Requests\Saas\UpdateSubscriptionPlanRequest;
use App\Models\Saas\SubscriptionPlan;
use App\Services\Saas\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends SaasController
{
    public function __construct(
        protected SubscriptionPlanService $plans,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SubscriptionPlan::class);

        if (! app(\App\Services\Rbac\AccessControlService::class)->isSuperAdmin($request->user())) {
            return response()->json([
                'data' => SubscriptionPlanResource::collection($this->plans->all($request->only(['status']))),
            ]);
        }

        $plans = $this->plans->paginate(
                $request->only(['search', 'status']),
                (int) $request->integer('per_page', 15),
            );

        return response()->json([
            'data' => SubscriptionPlanResource::collection($plans->getCollection()),
            'meta' => [
                'current_page' => $plans->currentPage(),
                'last_page' => $plans->lastPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
            ],
        ]);
    }

    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $this->authorize('create', SubscriptionPlan::class);

        $plan = $this->plans->createPlan($request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Subscription plan created successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ], 201);
    }

    public function update(UpdateSubscriptionPlanRequest $request, int $id): JsonResponse
    {
        $plan = $this->plans->findOrFail($id);
        $this->authorize('update', $plan);
        $plan = $this->plans->updatePlan($plan, $request->validated(), $request->user(), $request->ip());

        return response()->json([
            'message' => 'Subscription plan updated successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $plan = $this->plans->findOrFail($id);
        $this->authorize('delete', $plan);
        $this->plans->deletePlan($plan, $request->user(), $request->ip());

        return response()->json(null, 204);
    }

    public function addFeature(StorePlanFeatureRequest $request, int $id): JsonResponse
    {
        $plan = $this->plans->findOrFail($id);
        $this->authorize('update', $plan);
        $plan->loadMissing('planFeatures');

        $incoming = $request->validated();
        $features = $plan->planFeatures
            ->reject(fn ($feature) => $feature->feature_code === $incoming['feature_code'])
            ->map(fn ($feature) => [
                'feature_code' => $feature->feature_code,
                'feature_name' => $feature->feature_name,
                'module' => $feature->module,
                'is_enabled' => $feature->is_enabled,
                'limit_value' => $feature->limit_value,
            ])
            ->push([
                'feature_code' => $incoming['feature_code'],
                'feature_name' => $incoming['feature_name'],
                'module' => $incoming['module'],
                'is_enabled' => (bool) ($incoming['is_enabled'] ?? true),
                'limit_value' => $incoming['limit_value'] ?? null,
            ])
            ->values()
            ->all();

        $plan = $this->plans->managePlanFeatures($plan, $features);

        return response()->json([
            'message' => 'Plan features updated successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }
}
