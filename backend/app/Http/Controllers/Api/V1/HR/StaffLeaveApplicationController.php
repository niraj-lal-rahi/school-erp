<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\LeaveApplicationData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\ReviewStaffLeaveApplicationRequest;
use App\Http\Requests\HR\StoreStaffLeaveApplicationRequest;
use App\Http\Requests\HR\UpdateStaffLeaveApplicationRequest;
use App\Http\Resources\HR\StaffLeaveApplicationResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffLeaveApplication;
use App\Services\HR\StaffLeaveApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffLeaveApplicationController extends Controller
{
    public function __construct(protected StaffLeaveApplicationService $applications)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            StaffLeaveApplicationResource::collection($this->applications->paginate(
                $request->only(['search', 'staff_id', 'leave_type_id', 'status', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15)
            ))->response()->getData(true)
        );
    }

    public function store(StoreStaffLeaveApplicationRequest $request): JsonResponse
    {
        $staff = Staff::query()->findOrFail((int) $request->validated('staff_id'));
        $application = $this->applications->create($staff, LeaveApplicationData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Leave application created successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ], 201);
    }

    public function show(StaffLeaveApplication $leaveApplication): JsonResponse
    {
        return response()->json([
            'data' => new StaffLeaveApplicationResource($this->applications->show($leaveApplication)),
        ]);
    }

    public function update(UpdateStaffLeaveApplicationRequest $request, StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $application = $this->applications->update($leaveApplication, LeaveApplicationData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Leave application updated successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ]);
    }

    public function destroy(StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $this->applications->delete($leaveApplication);

        return response()->json(null, 204);
    }

    public function submit(StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $application = $this->applications->submit($leaveApplication);

        return response()->json([
            'message' => 'Leave application submitted successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ]);
    }

    public function approve(ReviewStaffLeaveApplicationRequest $request, StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $application = $this->applications->approve($leaveApplication, $request->user()->id, $request->validated('review_remarks'));

        return response()->json([
            'message' => 'Leave application approved successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ]);
    }

    public function reject(ReviewStaffLeaveApplicationRequest $request, StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $application = $this->applications->reject($leaveApplication, $request->user()->id, $request->validated('review_remarks'));

        return response()->json([
            'message' => 'Leave application rejected successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ]);
    }

    public function cancel(ReviewStaffLeaveApplicationRequest $request, StaffLeaveApplication $leaveApplication): JsonResponse
    {
        $application = $this->applications->cancel($leaveApplication, $request->user()->id, $request->validated('review_remarks'));

        return response()->json([
            'message' => 'Leave application cancelled successfully.',
            'data' => new StaffLeaveApplicationResource($application),
        ]);
    }
}
