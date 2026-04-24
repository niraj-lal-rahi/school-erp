<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\DepartmentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertDepartmentRequest;
use App\Http\Resources\HR\DepartmentResource;
use App\Models\HR\Department;
use App\Services\HR\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(
        protected DepartmentService $departments,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        return response()->json([
            'data' => DepartmentResource::collection(
                $this->departments->all($request->only(['search', 'status']))
            ),
        ]);
    }

    public function store(UpsertDepartmentRequest $request): JsonResponse
    {
        $department = $this->departments->create(DepartmentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Department created successfully.',
            'data' => new DepartmentResource($department->loadCount(['designations', 'staff'])),
        ], 201);
    }

    public function show(Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        return response()->json([
            'data' => new DepartmentResource($department->loadCount(['designations', 'staff'])),
        ]);
    }

    public function update(UpsertDepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->departments->update($department, DepartmentData::fromArray([
            ...$request->validated(),
            'school_id' => $department->school_id,
        ]));

        return response()->json([
            'message' => 'Department updated successfully.',
            'data' => new DepartmentResource($department),
        ]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->authorize('delete', $department);
        $this->departments->delete($department);

        return response()->json(null, 204);
    }
}
