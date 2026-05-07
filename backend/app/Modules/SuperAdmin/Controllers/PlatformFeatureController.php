<?php

namespace App\Modules\SuperAdmin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SuperAdmin\Services\PlatformFeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlatformFeatureController extends Controller
{
    public function __construct(
        protected PlatformFeatureService $features,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->features->list($request->only(['module']))->values(),
        ]);
    }

    public function update(Request $request, string $featureCode): JsonResponse
    {
        $payload = $request->validate([
            'module' => ['nullable', 'string', 'max:100'],
            'is_enabled' => ['required', 'boolean'],
            'label' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $feature = $this->features->update(
            $featureCode,
            $payload,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );

        return response()->json([
            'message' => 'Platform feature flag updated successfully.',
            'data' => $feature,
        ]);
    }
}
