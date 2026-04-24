<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\SalaryStructureData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreSalaryStructureRequest;
use App\Http\Requests\HR\UpdateSalaryStructureRequest;
use App\Http\Resources\HR\SalaryStructureResource;
use App\Models\HR\SalaryStructure;
use App\Models\HR\Staff;
use App\Services\HR\SalaryStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryStructureController extends Controller
{
    public function __construct(protected SalaryStructureService $structures)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => SalaryStructureResource::collection($this->structures->all($request->only(['staff_id', 'status']))),
        ]);
    }

    public function store(StoreSalaryStructureRequest $request): JsonResponse
    {
        $staff = Staff::query()->findOrFail((int) $request->validated('staff_id'));
        $structure = $this->structures->create($staff, SalaryStructureData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Salary structure created successfully.',
            'data' => new SalaryStructureResource($structure),
        ], 201);
    }

    public function show(SalaryStructure $salaryStructure): JsonResponse
    {
        return response()->json([
            'data' => new SalaryStructureResource($this->structures->show($salaryStructure)),
        ]);
    }

    public function update(UpdateSalaryStructureRequest $request, SalaryStructure $salaryStructure): JsonResponse
    {
        $structure = $this->structures->update($salaryStructure, SalaryStructureData::fromArray($request->validated()));

        return response()->json([
            'message' => 'Salary structure updated successfully.',
            'data' => new SalaryStructureResource($structure),
        ]);
    }

    public function destroy(SalaryStructure $salaryStructure): JsonResponse
    {
        $this->structures->delete($salaryStructure);

        return response()->json(null, 204);
    }
}
