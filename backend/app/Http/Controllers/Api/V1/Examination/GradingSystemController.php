<?php

namespace App\Http\Controllers\Api\V1\Examination;

use App\Http\Controllers\Controller;
use App\Http\Requests\Examination\StoreGradeScaleRequest;
use App\Http\Requests\Examination\StoreGradingSystemRequest;
use App\Http\Requests\Examination\UpdateGradingSystemRequest;
use App\Models\Examination\GradeScale;
use App\Models\Examination\GradingSystem;
use App\Services\Examination\GradingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradingSystemController extends Controller
{
    public function __construct(
        protected GradingService $grading,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->grading->paginate(
                $request->only(['search', 'status', 'grading_type', 'result_status']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreGradingSystemRequest $request): JsonResponse
    {
        $gradingSystem = $this->grading->createSystem([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
        ]);

        return response()->json([
            'message' => 'Grading system created successfully.',
            'data' => $gradingSystem,
        ], 201);
    }

    public function show(GradingSystem $gradingSystem): JsonResponse
    {
        return response()->json([
            'data' => $this->grading->findSystemOrFail($gradingSystem->id),
        ]);
    }

    public function update(UpdateGradingSystemRequest $request, GradingSystem $gradingSystem): JsonResponse
    {
        $gradingSystem = $this->grading->updateSystem($gradingSystem, $request->validated());

        return response()->json([
            'message' => 'Grading system updated successfully.',
            'data' => $gradingSystem,
        ]);
    }

    public function destroy(GradingSystem $gradingSystem): JsonResponse
    {
        $this->grading->deleteSystem($gradingSystem);

        return response()->json(null, 204);
    }

    public function addScale(StoreGradeScaleRequest $request, GradingSystem $gradingSystem): JsonResponse
    {
        $scale = $this->grading->addScale($gradingSystem, $request->validated());

        return response()->json([
            'message' => 'Grade scale added successfully.',
            'data' => $scale,
        ], 201);
    }

    public function removeScale(GradeScale $gradeScale): JsonResponse
    {
        $this->grading->removeScale($gradeScale);

        return response()->json(null, 204);
    }
}
