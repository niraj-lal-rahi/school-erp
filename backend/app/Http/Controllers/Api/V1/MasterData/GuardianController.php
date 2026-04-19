<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreGuardianRequest;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class GuardianController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Guardian::query()
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get(),
        ]);
    }

    public function store(StoreGuardianRequest $request): JsonResponse
    {
        $guardian = Guardian::create([
            'uuid' => (string) Str::uuid(),
            ...$request->validated(),
        ]);

        return response()->json([
            'message' => 'Guardian created successfully.',
            'data' => $guardian,
        ], 201);
    }
}
