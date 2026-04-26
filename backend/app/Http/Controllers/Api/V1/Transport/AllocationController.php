<?php

namespace App\Http\Controllers\Api\V1\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transport\UpsertStaffTransportAllocationRequest;
use App\Http\Requests\Transport\UpsertStudentTransportAllocationRequest;
use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\StudentTransportAllocation;
use App\Services\Transport\TransportAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllocationController extends Controller
{
    public function __construct(
        protected TransportAllocationService $allocations,
    ) {
    }

    public function studentIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->allocations->paginateStudentAllocations(
                $request->only(['student_id', 'academic_year_id', 'route_id', 'status', 'search']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function studentStore(UpsertStudentTransportAllocationRequest $request): JsonResponse
    {
        $allocation = $this->allocations->createStudentAllocation([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Student transport allocation created successfully.',
            'data' => $allocation,
        ], 201);
    }

    public function studentShow(StudentTransportAllocation $allocation): JsonResponse
    {
        return response()->json([
            'data' => $this->allocations->showStudentAllocation($allocation),
        ]);
    }

    public function studentUpdate(
        UpsertStudentTransportAllocationRequest $request,
        StudentTransportAllocation $allocation,
    ): JsonResponse {
        $allocation = $this->allocations->updateStudentAllocation($allocation, $request->validated());

        return response()->json([
            'message' => 'Student transport allocation updated successfully.',
            'data' => $allocation,
        ]);
    }

    public function studentDestroy(StudentTransportAllocation $allocation): JsonResponse
    {
        $this->allocations->deleteStudentAllocation($allocation);

        return response()->json(null, 204);
    }

    public function staffIndex(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->allocations->paginateStaffAllocations(
                $request->only(['staff_id', 'route_id', 'status', 'search']),
                (int) $request->integer('per_page', 15)
            ),
        ]);
    }

    public function staffStore(UpsertStaffTransportAllocationRequest $request): JsonResponse
    {
        $allocation = $this->allocations->createStaffAllocation([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Staff transport allocation created successfully.',
            'data' => $allocation,
        ], 201);
    }

    public function staffShow(StaffTransportAllocation $allocation): JsonResponse
    {
        return response()->json([
            'data' => $this->allocations->showStaffAllocation($allocation),
        ]);
    }

    public function staffUpdate(
        UpsertStaffTransportAllocationRequest $request,
        StaffTransportAllocation $allocation,
    ): JsonResponse {
        $allocation = $this->allocations->updateStaffAllocation($allocation, $request->validated());

        return response()->json([
            'message' => 'Staff transport allocation updated successfully.',
            'data' => $allocation,
        ]);
    }

    public function staffDestroy(StaffTransportAllocation $allocation): JsonResponse
    {
        $this->allocations->deleteStaffAllocation($allocation);

        return response()->json(null, 204);
    }
}
