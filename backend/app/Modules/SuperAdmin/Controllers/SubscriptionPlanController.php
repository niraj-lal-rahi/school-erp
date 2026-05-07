<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Resources\SubscriptionPlanResource;
use App\Modules\SuperAdmin\Services\SubscriptionPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        protected SubscriptionPlanService $plans,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
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

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:platform.subscription_plans,code'],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $plan = $this->plans->create($payload, $request->user()?->id, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'Subscription plan created successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $plan = $this->plans->findOrFail($id);

        return response()->json([
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => ['sometimes', 'required', 'string', 'max:100', 'alpha_dash', 'unique:platform.subscription_plans,code,'.$id],
            'description' => ['nullable', 'string'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'max_students' => ['nullable', 'integer', 'min:0'],
            'max_staff' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $plan = $this->plans->update(
            $this->plans->findOrFail($id),
            $payload,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Subscription plan updated successfully.',
            'data' => new SubscriptionPlanResource($plan),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->plans->delete(
            $this->plans->findOrFail($id),
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json(null, 204);
    }
}
