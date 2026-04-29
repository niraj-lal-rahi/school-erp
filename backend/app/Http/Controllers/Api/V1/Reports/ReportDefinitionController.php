<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StoreReportDefinitionRequest;
use App\Http\Requests\Reports\UpdateReportDefinitionRequest;
use App\Models\Reports\ReportDefinition;
use App\Services\Reports\ReportExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportDefinitionController extends Controller
{
    public function __construct(
        protected ReportExecutionService $reports,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->paginateDefinitions(
                $request->only(['search', 'module', 'status', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreReportDefinitionRequest $request): JsonResponse
    {
        $reportDefinition = $this->reports->createDefinition([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Report definition created successfully.',
            'data' => $reportDefinition,
        ], 201);
    }

    public function show(ReportDefinition $reportDefinition): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->findDefinitionOrFail($reportDefinition->id),
        ]);
    }

    public function update(UpdateReportDefinitionRequest $request, ReportDefinition $reportDefinition): JsonResponse
    {
        $reportDefinition = $this->reports->updateDefinition($reportDefinition, $request->validated());

        return response()->json([
            'message' => 'Report definition updated successfully.',
            'data' => $reportDefinition,
        ]);
    }

    public function destroy(ReportDefinition $reportDefinition): JsonResponse
    {
        $this->reports->deleteDefinition($reportDefinition);

        return response()->json(null, 204);
    }
}
