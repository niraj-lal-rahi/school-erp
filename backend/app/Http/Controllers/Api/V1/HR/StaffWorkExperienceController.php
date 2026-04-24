<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffWorkExperienceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertStaffWorkExperienceRequest;
use App\Http\Resources\HR\StaffWorkExperienceResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffWorkExperience;
use App\Services\HR\StaffWorkExperienceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffWorkExperienceController extends Controller
{
    public function __construct(
        protected StaffWorkExperienceService $experiences,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json([
            'data' => StaffWorkExperienceResource::collection(
                $this->experiences->all($request->only(['staff_id']))
            ),
        ]);
    }

    public function store(UpsertStaffWorkExperienceRequest $request, Staff $staff): JsonResponse
    {
        $experience = $this->experiences->create($staff, StaffWorkExperienceData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Work experience created successfully.',
            'data' => new StaffWorkExperienceResource($experience),
        ], 201);
    }

    public function update(UpsertStaffWorkExperienceRequest $request, StaffWorkExperience $staffWorkExperience): JsonResponse
    {
        $experience = $this->experiences->update($staffWorkExperience, StaffWorkExperienceData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Work experience updated successfully.',
            'data' => new StaffWorkExperienceResource($experience),
        ]);
    }

    public function destroy(StaffWorkExperience $staffWorkExperience): JsonResponse
    {
        $this->authorize('update', $staffWorkExperience->staff);
        $this->experiences->delete($staffWorkExperience);

        return response()->json(null, 204);
    }

    public function staffExperiences(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffWorkExperienceResource::collection($this->experiences->allForStaff($staff)),
        ]);
    }
}
