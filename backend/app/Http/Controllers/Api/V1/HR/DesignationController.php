<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\DesignationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertDesignationRequest;
use App\Http\Resources\HR\DesignationResource;
use App\Models\HR\Designation;
use App\Services\HR\DesignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct(
        protected DesignationService $designations,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Designation::class);

        return response()->json([
            'data' => DesignationResource::collection(
                $this->designations->all($request->only(['search', 'status', 'department_id']))
            ),
        ]);
    }

    public function store(UpsertDesignationRequest $request): JsonResponse
    {
        $designation = $this->designations->create(DesignationData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Designation created successfully.',
            'data' => new DesignationResource($designation->load(['department'])->loadCount('staff')),
        ], 201);
    }

    public function show(Designation $designation): JsonResponse
    {
        $this->authorize('view', $designation);

        return response()->json([
            'data' => new DesignationResource($designation->load(['department'])->loadCount('staff')),
        ]);
    }

    public function update(UpsertDesignationRequest $request, Designation $designation): JsonResponse
    {
        $designation = $this->designations->update($designation, DesignationData::fromArray([
            ...$request->validated(),
            'school_id' => $designation->school_id,
        ]));

        return response()->json([
            'message' => 'Designation updated successfully.',
            'data' => new DesignationResource($designation),
        ]);
    }

    public function destroy(Designation $designation): JsonResponse
    {
        $this->authorize('delete', $designation);
        $this->designations->delete($designation);

        return response()->json(null, 204);
    }
}
