<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Resources\PlanFeatureResource;
use App\Modules\SuperAdmin\Resources\SubscriptionPlanResource;
use App\Modules\SuperAdmin\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanFeatureController extends Controller
{
    public function __construct(
        protected SubscriptionPlanService $plans,
    ) {
    }

    public function index(int $id): JsonResponse
    {
        $plan = $this->plans->findOrFail($id);

        return response()->json([
            'data' => PlanFeatureResource::collection($plan->features),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'features' => ['required', 'array'],
            'features.*.feature_code' => ['required', 'string', 'max:100'],
            'features.*.feature_name' => ['required', 'string', 'max:255'],
            'features.*.module' => ['required', 'string', 'max:100'],
            'features.*.is_enabled' => ['nullable', 'boolean'],
            'features.*.limit_value' => ['nullable', 'integer', 'min:0'],
            'features.*.config' => ['nullable', 'array'],
        ]);

        $plan = $this->plans->syncFeatures(
            $this->plans->findOrFail($id),
            $payload['features'],
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Plan feature matrix updated successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }
}
