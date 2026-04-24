<?php

namespace App\Http\Controllers\Api\V1\HR;

use App\DataTransferObjects\HR\SalaryComponentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpsertSalaryComponentRequest;
use App\Http\Resources\HR\SalaryComponentResource;
use App\Models\HR\SalaryComponent;
use App\Services\HR\SalaryComponentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function __construct(protected SalaryComponentService $components)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => SalaryComponentResource::collection($this->components->all($request->only(['search', 'component_type', 'status']))),
        ]);
    }

    public function store(UpsertSalaryComponentRequest $request): JsonResponse
    {
        $component = $this->components->create(SalaryComponentData::fromArray([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]));

        return response()->json([
            'message' => 'Salary component created successfully.',
            'data' => new SalaryComponentResource($component),
        ], 201);
    }

    public function show(SalaryComponent $salaryComponent): JsonResponse
    {
        return response()->json([
            'data' => new SalaryComponentResource($salaryComponent),
        ]);
    }

    public function update(UpsertSalaryComponentRequest $request, SalaryComponent $salaryComponent): JsonResponse
    {
        $component = $this->components->update($salaryComponent, SalaryComponentData::fromArray([
            ...$request->validated(),
            'school_id' => $salaryComponent->school_id,
        ]));

        return response()->json([
            'message' => 'Salary component updated successfully.',
            'data' => new SalaryComponentResource($component),
        ]);
    }

    public function destroy(SalaryComponent $salaryComponent): JsonResponse
    {
        $this->components->delete($salaryComponent);

        return response()->json(null, 204);
    }
}
