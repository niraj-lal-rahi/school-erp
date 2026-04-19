<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\StoreGuardianRequest;
use App\Models\Guardian;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuardianController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => Guardian::query()
                ->when($request->string('search')->toString(), function ($query, string $search): void {
                    $query->where(function ($guardianQuery) use ($search): void {
                        $guardianQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                })
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

    public function update(StoreGuardianRequest $request, Guardian $guardian): JsonResponse
    {
        $guardian->update($request->validated());

        return response()->json([
            'message' => 'Guardian updated successfully.',
            'data' => $guardian->fresh(),
        ]);
    }

    public function destroy(Guardian $guardian): JsonResponse
    {
        $guardian->delete();

        return response()->json(null, 204);
    }
}
