<?php

namespace App\Http\Controllers\Api\V1\Finance;

use App\DataTransferObjects\Finance\FeeStructureData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\UpsertFeeStructureRequest;
use App\Http\Resources\Finance\FeeStructureResource;
use App\Models\Finance\FeeStructure;
use App\Services\Finance\FeeStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeeStructureController extends Controller
{
    public function __construct(
        protected FeeStructureService $feeStructures,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', FeeStructure::class);

        return response()->json([
            'data' => FeeStructureResource::collection(
                $this->feeStructures->all($request->only(['search', 'academic_year_id', 'school_class_id', 'section_id', 'status']))
            ),
        ]);
    }

    public function store(UpsertFeeStructureRequest $request): JsonResponse
    {
        $feeStructure = $this->feeStructures->create(FeeStructureData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Fee structure created successfully.',
            'data' => new FeeStructureResource($feeStructure),
        ], 201);
    }

    public function show(FeeStructure $feeStructure): JsonResponse
    {
        $this->authorize('view', $feeStructure);

        return response()->json([
            'data' => new FeeStructureResource($feeStructure->load(['academicYear', 'schoolClass', 'section', 'items.feeHead'])),
        ]);
    }

    public function update(UpsertFeeStructureRequest $request, FeeStructure $feeStructure): JsonResponse
    {
        $feeStructure = $this->feeStructures->update($feeStructure, FeeStructureData::fromArray([
            ...$request->validated(),
            'school_id' => $feeStructure->school_id,
        ]));

        return response()->json([
            'message' => 'Fee structure updated successfully.',
            'data' => new FeeStructureResource($feeStructure),
        ]);
    }

    public function destroy(FeeStructure $feeStructure): JsonResponse
    {
        $this->authorize('delete', $feeStructure);
        $this->feeStructures->delete($feeStructure);

        return response()->json(null, 204);
    }
}
