<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffBankDetailData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertStaffBankDetailRequest;
use App\Http\Resources\HR\StaffBankDetailResource;
use App\Models\HR\Staff;
use App\Models\HR\StaffBankDetail;
use App\Services\HR\StaffBankDetailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffBankDetailController extends Controller
{
    public function __construct(protected StaffBankDetailService $bankDetails)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json([
            'data' => StaffBankDetailResource::collection($this->bankDetails->all($request->only(['staff_id']))),
        ]);
    }

    public function store(UpsertStaffBankDetailRequest $request, Staff $staff): JsonResponse
    {
        $bankDetail = $this->bankDetails->create($staff, StaffBankDetailData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Bank detail created successfully.',
            'data' => new StaffBankDetailResource($bankDetail),
        ], 201);
    }

    public function update(UpsertStaffBankDetailRequest $request, StaffBankDetail $staffBankDetail): JsonResponse
    {
        $bankDetail = $this->bankDetails->update($staffBankDetail, StaffBankDetailData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Bank detail updated successfully.',
            'data' => new StaffBankDetailResource($bankDetail),
        ]);
    }

    public function destroy(StaffBankDetail $staffBankDetail): JsonResponse
    {
        $this->authorize('update', $staffBankDetail->staff);
        $this->bankDetails->delete($staffBankDetail);

        return response()->json(null, 204);
    }

    public function staffBankDetails(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => StaffBankDetailResource::collection($this->bankDetails->allForStaff($staff)),
        ]);
    }
}
