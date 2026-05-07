<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Models\PlatformTenant;
use App\Modules\SuperAdmin\Services\TenantFeatureAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantFeatureAccessController extends Controller
{
    public function __construct(
        protected TenantFeatureAccessService $features,
    ) {
    }

    public function index(Request $request, int $id): JsonResponse
    {
        $tenant = PlatformTenant::query()->findOrFail($id);

        return response()->json([
            'data' => $this->features->listForTenant($tenant, $request->only(['module']))->values(),
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $payload = $request->validate([
            'features' => ['required', 'array'],
            'features.*.feature_code' => ['required', 'string', 'max:100'],
            'features.*.module' => ['required', 'string', 'max:100'],
            'features.*.is_enabled' => ['required', 'boolean'],
            'features.*.limit_value' => ['nullable', 'integer', 'min:0'],
            'features.*.access_source' => ['nullable', 'in:plan,override,trial'],
            'features.*.subscription_plan_id' => ['nullable', 'integer'],
            'features.*.metadata' => ['nullable', 'array'],
        ]);

        $tenant = PlatformTenant::query()->findOrFail($id);

        $features = $this->features->syncTenantOverrides(
            $tenant,
            $payload['features'],
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Tenant feature access updated successfully.',
            'data' => $features->values(),
        ]);
    }
}
