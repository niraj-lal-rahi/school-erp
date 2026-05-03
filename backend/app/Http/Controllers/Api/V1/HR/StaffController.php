<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\StaffData;
use App\DataTransferObjects\HR\StaffStatusActionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreStaffRequest;
use App\Http\Requests\HR\StaffStatusActionRequest;
use App\Http\Requests\HR\UpdateStaffRequest;
use App\Http\Resources\HR\StaffListResource;
use App\Http\Resources\HR\StaffResource;
use App\Models\HR\Staff;
use App\Services\HR\StaffLifecycleService;
use App\Services\HR\StaffService;
use App\Support\Api\ApiPaginationHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        protected StaffService $staff,
        protected StaffLifecycleService $lifecycle,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Staff::class);

        return response()->json(
            ApiPaginationHelper::fromResourceCollection(StaffListResource::collection($this->staff->paginate(
                filters: $request->only([
                    'search',
                    'department_id',
                    'designation_id',
                    'staff_type',
                    'employment_type',
                    'current_status',
                ]),
                perPage: (int) $request->integer('per_page', 15),
            )))
        );
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $staff = $this->staff->create(
            StaffData::fromArray([
                ...$request->validated(),
                'school_id' => $request->user()->school_id,
            ]),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Staff created successfully.',
            'data' => new StaffResource($staff),
        ], 201);
    }

    public function show(Staff $staff): JsonResponse
    {
        $this->authorize('view', $staff);

        return response()->json([
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->staff->update(
            $staff,
            StaffData::fromArray([
                ...$request->validated(),
                'school_id' => $staff->school_id,
            ]),
            $request->user()->id,
        );

        return response()->json([
            'message' => 'Staff updated successfully.',
            'data' => new StaffResource($staff),
        ]);
    }

    public function destroy(Staff $staff): JsonResponse
    {
        $this->authorize('delete', $staff);
        $this->staff->delete($staff);

        return response()->json(null, 204);
    }

    public function activate(StaffStatusActionRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->lifecycle->activate($staff, StaffStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff activated successfully.',
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }

    public function suspend(StaffStatusActionRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->lifecycle->suspend($staff, StaffStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff suspended successfully.',
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }

    public function resign(StaffStatusActionRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->lifecycle->resign($staff, StaffStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff marked as resigned successfully.',
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }

    public function terminate(StaffStatusActionRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->lifecycle->terminate($staff, StaffStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff terminated successfully.',
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }

    public function retire(StaffStatusActionRequest $request, Staff $staff): JsonResponse
    {
        $staff = $this->lifecycle->retire($staff, StaffStatusActionData::fromArray($request->validated()), $request->user()->id);

        return response()->json([
            'message' => 'Staff retired successfully.',
            'data' => new StaffResource($this->staff->show($staff)),
        ]);
    }
}
