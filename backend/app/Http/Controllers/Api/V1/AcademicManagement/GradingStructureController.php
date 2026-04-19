<?php

namespace App\Http\Controllers\Api\V1\AcademicManagement;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicManagement\UpsertGradingStructureRequest;
use App\Http\Resources\AcademicManagement\GradingStructureResource;
use App\Models\AcademicManagement\GradingStructure;
use App\Services\AcademicManagement\GradingStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradingStructureController extends Controller
{
    public function __construct(protected GradingStructureService $service)
    {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', GradingStructure::class);

        return GradingStructureResource::collection($this->service->paginate($request->only(['search', 'academic_year_id', 'status']), (int) $request->integer('per_page', 15)));
    }

    public function store(UpsertGradingStructureRequest $request): JsonResponse
    {
        $this->authorize('create', GradingStructure::class);
        $record = $this->service->create($request->validated());

        return response()->json(['message' => 'Grading structure created successfully.', 'data' => new GradingStructureResource($record)], 201);
    }

    public function show(GradingStructure $gradingStructure): GradingStructureResource
    {
        $this->authorize('view', $gradingStructure);

        return new GradingStructureResource($this->service->show($gradingStructure->id));
    }

    public function update(UpsertGradingStructureRequest $request, GradingStructure $gradingStructure): JsonResponse
    {
        $this->authorize('update', $gradingStructure);
        $updated = $this->service->update($gradingStructure, $request->validated());

        return response()->json(['message' => 'Grading structure updated successfully.', 'data' => new GradingStructureResource($updated)]);
    }

    public function destroy(GradingStructure $gradingStructure): JsonResponse
    {
        $this->authorize('delete', $gradingStructure);
        $this->service->delete($gradingStructure);

        return response()->json(null, 204);
    }
}
