<?php

namespace App\Http\Controllers\Api\V1\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\StoreReportScheduleRequest;
use App\Http\Requests\Reports\UpdateReportScheduleRequest;
use App\Models\Reports\ReportSchedule;
use App\Services\Reports\ReportExecutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportScheduleController extends Controller
{
    public function __construct(
        protected ReportExecutionService $reports,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->paginateSchedules(
                $request->only(['module', 'status', 'report_definition_id', 'date_from', 'date_to']),
                (int) $request->integer('per_page', 15),
            ),
        ]);
    }

    public function store(StoreReportScheduleRequest $request): JsonResponse
    {
        $reportSchedule = $this->reports->createSchedule([
            ...$request->validated(),
            'school_id' => $request->user()->school_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Report schedule created successfully.',
            'data' => $reportSchedule,
        ], 201);
    }

    public function show(ReportSchedule $reportSchedule): JsonResponse
    {
        return response()->json([
            'data' => $this->reports->findScheduleOrFail($reportSchedule->id),
        ]);
    }

    public function update(UpdateReportScheduleRequest $request, ReportSchedule $reportSchedule): JsonResponse
    {
        $reportSchedule = $this->reports->updateSchedule($reportSchedule, $request->validated());

        return response()->json([
            'message' => 'Report schedule updated successfully.',
            'data' => $reportSchedule,
        ]);
    }

    public function pause(ReportSchedule $reportSchedule): JsonResponse
    {
        $reportSchedule = $this->reports->pauseSchedule($reportSchedule);

        return response()->json([
            'message' => 'Report schedule paused successfully.',
            'data' => $reportSchedule,
        ]);
    }

    public function resume(Request $request, ReportSchedule $reportSchedule): JsonResponse
    {
        $request->validate([
            'next_run_at' => ['nullable', 'date'],
        ]);

        $reportSchedule = $this->reports->resumeSchedule($reportSchedule, $request->input('next_run_at'));

        return response()->json([
            'message' => 'Report schedule resumed successfully.',
            'data' => $reportSchedule,
        ]);
    }
}
