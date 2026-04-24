<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffQualificationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertStaffQualificationRequest;
use App\Http\Resources\HR\StaffQualificationResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffQualification;
use App\Services\HR\StaffQualificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffQualificationController extends Controller
{
    public function __construct(
        protected StaffQualificationService $qualifications,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json([
            'data' => StaffQualificationResource::collection(
                $this->qualifications->all($request->only(['staff_id']))
            ),
        ]);
    }

    public function store(UpsertStaffQualificationRequest $request, Staff $staff): JsonResponse
    {
        $qualification = $this->qualifications->create($staff, StaffQualificationData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Qualification created successfully.',
            'data' => new StaffQualificationResource($qualification),
        ], 201);
    }

    public function update(UpsertStaffQualificationRequest $request, StaffQualification $staffQualification): JsonResponse
    {
        $qualification = $this->qualifications->update($staffQualification, StaffQualificationData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Qualification updated successfully.',
            'data' => new StaffQualificationResource($qualification),
        ]);
    }

    public function destroy(StaffQualification $staffQualification): JsonResponse
    {
        $this->authorize('update', $staffQualification->staff);
        $this->qualifications->delete($staffQualification);

        return response()->json(null, 204);
    }

    public function staffQualifications(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffQualificationResource::collection($this->qualifications->allForStaff($staff)),
        ]);
    }
}
