<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\RunReportRequest;
use App\Models\Reports\ReportRun;
use App\Services\Reports\ReportExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportRunController extends Controller
{
    public function __construct(
        protected ReportExecutionService $reports,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->paginateRuns(
                $request->only(['module', 'status', 'report_definition_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function show(ReportRun $reportRun): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->findRunOrFail($reportRun->id),
        ]);
    }

    public function run(RunReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $reportRun = $this->reports->runReport(
            ['report_definition_id' => (int) $validated['report_definition_id']],
            $validated['parameters'] ?? [],
            $validated['file_type'],
            'manual',
            $request->user()->id,
            [
                'source' => 'api',
                'requested_by' => $request->user()->id,
            ],
        );

        return response()->json([
            'message' => 'Report executed successfully.',
            'data' => $reportRun,
        ], 201);
    }
}
