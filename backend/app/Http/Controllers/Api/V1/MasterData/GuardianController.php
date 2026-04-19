<?php

namespace App\Http\Controllers\Api\V1\MasterData;

use App\DataTransferObjects\SIS\GuardianData;
use App\Http\Controllers\Controller;
use App\Http\Requests\SIS\UpsertGuardianRequest;
use App\Http\Resources\SIS\GuardianResource;
use App\Models\Guardian;
use App\Services\SIS\GuardianService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuardianController extends Controller
{
    public function __construct(
        protected GuardianService $guardians,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Guardian::class);

        return response()->json([
            'data' => GuardianResource::collection(
                $this->guardians->all($request->only(['search']))
            ),
        ]);
    }

    public function store(UpsertGuardianRequest $request): JsonResponse
    {
        $guardian = $this->guardians->create(GuardianData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Guardian created successfully.',
            'data' => new GuardianResource($guardian),
        ], 201);
    }

    public function update(UpsertGuardianRequest $request, Guardian $guardian): JsonResponse
    {
        $guardian = $this->guardians->update($guardian, GuardianData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Guardian updated successfully.',
            'data' => new GuardianResource($guardian),
        ]);
    }

    public function destroy(Guardian $guardian): JsonResponse
    {
        $this->authorize('delete', $guardian);
        $this->guardians->delete($guardian);

        return response()->json(null, 204);
    }
}
