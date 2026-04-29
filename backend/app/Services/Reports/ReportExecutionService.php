<?php

namespace App\Services\Reports;

use App\Events\Reports\ReportRunCompleted;
use App\Events\Reports\ReportRunFailed;
use App\Events\Reports\ReportRunStarted;
use App\Models\Reports\ReportDefinition;
use App\Models\Reports\ReportRun;
use App\Models\Reports\ReportSchedule;
use App\Repositories\Contracts\Reports\ReportDefinitionRepositoryInterface;
use App\Repositories\Contracts\Reports\ReportRunRepositoryInterface;
use App\Repositories\Contracts\Reports\ReportScheduleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ReportExecutionService
{
    public function __construct(
        protected ReportDefinitionRepositoryInterface $definitions,
        protected ReportScheduleRepositoryInterface $schedules,
        protected ReportRunRepositoryInterface $runs,
        protected DashboardService $dashboardReports,
        protected AttendanceReportService $attendanceReports,
        protected FinanceReportService $financeReports,
        protected ExamReportService $examReports,
        protected TransportReportService $transportReports,
        protected CommunicationReportService $communicationReports,
        protected CustomReportService $customReports,
        protected ReportExportService $exports,
        protected ReportCacheService $cache,
    ) {
    }

    public function paginateDefinitions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->definitions->paginate($filters, $perPage);
    }

    public function findDefinitionOrFail(int $id): ReportDefinition
    {
        return $this->definitions->findOrFail($id);
    }

    public function createDefinition(array $attributes): ReportDefinition
    {
        return DB::transaction(fn (): ReportDefinition => $this->definitions->create($attributes));
    }

    public function updateDefinition(ReportDefinition $reportDefinition, array $attributes): ReportDefinition
    {
        return DB::transaction(fn (): ReportDefinition => $this->definitions->update($reportDefinition, $attributes));
    }

    public function deleteDefinition(ReportDefinition $reportDefinition): void
    {
        DB::transaction(function () use ($reportDefinition): void {
            $this->definitions->delete($reportDefinition);
        });
    }

    public function paginateSchedules(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->schedules->paginate($filters, $perPage);
    }

    public function findScheduleOrFail(int $id): ReportSchedule
    {
        return $this->schedules->findOrFail($id);
    }

    public function createSchedule(array $attributes): ReportSchedule
    {
        return DB::transaction(fn (): ReportSchedule => $this->schedules->create($attributes));
    }

    public function updateSchedule(ReportSchedule $reportSchedule, array $attributes): ReportSchedule
    {
        return DB::transaction(fn (): ReportSchedule => $this->schedules->update($reportSchedule, $attributes));
    }

    public function pauseSchedule(ReportSchedule $reportSchedule): ReportSchedule
    {
        return DB::transaction(fn (): ReportSchedule => $this->schedules->update($reportSchedule, ['status' => 'paused']));
    }

    public function resumeSchedule(ReportSchedule $reportSchedule, ?string $nextRunAt = null): ReportSchedule
    {
        return DB::transaction(fn (): ReportSchedule => $this->schedules->update($reportSchedule, [
            'status' => 'active',
            'next_run_at' => $nextRunAt ?? $reportSchedule->next_run_at,
        ]));
    }

    public function paginateRuns(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->runs->paginate($filters, $perPage);
    }

    public function findRunOrFail(int $id): ReportRun
    {
        return $this->runs->findOrFail($id);
    }

    public function runReport(
        array|ReportDefinition $definition,
        array $parameters = [],
        string $fileType = 'json',
        string $runType = 'manual',
        ?int $initiatedBy = null,
        array $context = [],
    ): ReportRun
    {
        $reportDefinition = $definition instanceof ReportDefinition
            ? $definition
            : $this->definitions->findOrFail((int) $definition['report_definition_id']);

        $run = DB::transaction(fn (): ReportRun => $this->runs->create([
            'school_id' => $reportDefinition->school_id,
            'report_definition_id' => $reportDefinition->id,
            'run_type' => $runType,
            'status' => 'pending',
            'started_at' => null,
            'completed_at' => null,
            'file_path' => null,
            'file_type' => $fileType,
            'parameters' => $parameters,
            'error_message' => null,
            'initiated_by' => $initiatedBy,
        ]));

        try {
            return $this->processRun($run, $context);
        } catch (Throwable $throwable) {
            $failedRun = $this->runs->update($run, [
                'status' => 'failed',
                'completed_at' => now(),
                'error_message' => $throwable->getMessage(),
            ]);
            event(new ReportRunFailed($failedRun, $throwable->getMessage(), $context));

            throw $throwable;
        }
    }

    public function processRun(ReportRun $reportRun, array $context = []): ReportRun
    {
        $reportRun = $this->runs->update($reportRun, [
            'status' => 'processing',
            'started_at' => now(),
            'error_message' => null,
        ]);
        event(new ReportRunStarted($reportRun, $context));

        $definition = $reportRun->reportDefinition;
        if (! $definition) {
            throw ValidationException::withMessages([
                'report_definition_id' => ['The selected report definition could not be resolved.'],
            ]);
        }

        $filters = array_merge($definition->default_filters ?? [], $reportRun->parameters ?? []);
        $cacheKey = $this->cache->buildKey('reports.run.'.$definition->code, $filters);

        $data = $this->cache->remember($reportRun->school_id, $cacheKey, fn () => $this->resolveData($definition, $filters), 900);
        $export = $this->exports->export($reportRun, $data, $reportRun->file_type);

        $completedRun = $this->runs->update($reportRun, [
            'status' => 'completed',
            'completed_at' => now(),
            'file_path' => $export->file_path,
            'error_message' => null,
        ]);

        event(new ReportRunCompleted($completedRun, $data, array_merge($context, [
            'cache_key' => $cacheKey,
            'filters' => $filters,
            'export_id' => $export->id,
            'export_file_name' => $export->file_name,
            'export_file_path' => $export->file_path,
            'run_type' => $completedRun->run_type,
        ])));

        return $completedRun;
    }

    protected function resolveData(ReportDefinition $definition, array $filters): array
    {
        return match ($definition->module) {
            'dashboard' => $this->dashboardReports->overview($filters),
            'attendance' => $this->attendanceReports->overview($filters),
            'finance' => $this->financeReports->overview($filters),
            'exams' => $this->examReports->overview($filters),
            'transport' => $this->transportReports->overview($filters),
            'communication' => $this->communicationReports->overview($filters),
            'custom' => $this->customReports->run($definition, $filters),
            default => throw ValidationException::withMessages([
                'module' => ['Unsupported report module: '.$definition->module],
            ]),
        };
    }
}
