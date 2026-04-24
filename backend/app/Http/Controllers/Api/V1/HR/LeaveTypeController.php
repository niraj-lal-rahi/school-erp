<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\LeaveTypeData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertLeaveTypeRequest;
use App\Http\Resources\HR\LeaveTypeResource;
use App\Models\HR\LeaveType;
use App\Services\HR\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(protected LeaveTypeService $leaveTypes)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => LeaveTypeResource::collection($this->leaveTypes->all($request->only(['search', 'status']))),
        ]);
    }

    public function store(UpsertLeaveTypeRequest $request): JsonResponse
    {
        $leaveType = $this->leaveTypes->create(LeaveTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Leave type created successfully.',
            'data' => new LeaveTypeResource($leaveType),
        ], 201);
    }

    public function show(LeaveType $leaveType): JsonResponse
    {
        return response()->json([
            'data' => new LeaveTypeResource($leaveType),
        ]);
    }

    public function update(UpsertLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        $leaveType = $this->leaveTypes->update($leaveType, LeaveTypeData::fromArray([
            ...$request->validated(),
            'school_id' => $leaveType->school_id,
        ]));

        return response()->json([
            'message' => 'Leave type updated successfully.',
            'data' => new LeaveTypeResource($leaveType),
        ]);
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        $this->leaveTypes->delete($leaveType);

        return response()->json(null, 204);
    }
}
