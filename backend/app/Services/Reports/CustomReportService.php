<?php

namespace App\Services\Reports;

use App\Models\Reports\ReportDefinition;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CustomReportService
{
    public function __construct(
        protected DashboardService $dashboardReports,
        protected AttendanceReportService $attendanceReports,
        protected FinanceReportService $financeReports,
        protected ExamReportService $examReports,
        protected TransportReportService $transportReports,
        protected CommunicationReportService $communicationReports,
    ) {
    }

    public function run(ReportDefinition|array $definition, array $filters = []): array
    {
        $config = $definition instanceof ReportDefinition ? ($definition->query_config ?? []) : $definition;
        $module = $definition instanceof ReportDefinition ? $definition->module : ($config['module'] ?? 'custom');
        $mergedFilters = array_merge($definition instanceof ReportDefinition ? ($definition->default_filters ?? []) : [], $filters);

        if ($module !== 'custom') {
            return $this->runModuleReport($module, $config['report_key'] ?? null, $mergedFilters);
        }

        return $this->runCustomQuery($config, $mergedFilters);
    }

    protected function runModuleReport(string $module, ?string $reportKey, array $filters): array
    {
        return match ($module) {
            'dashboard' => $this->runDashboardReport($reportKey, $filters),
            'attendance' => $this->runAttendanceReport($reportKey, $filters),
            'finance' => $this->runFinanceReport($reportKey, $filters),
            'exams' => $this->runExamReport($reportKey, $filters),
            'transport' => $this->runTransportReport($reportKey, $filters),
            'communication' => $this->runCommunicationReport($reportKey, $filters),
            default => throw ValidationException::withMessages([
                'module' => ['Unsupported report module: '.$module],
            ]),
        };
    }

    protected function runDashboardReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'widgets' => ['rows' => $this->dashboardReports->widgets(filters: $filters)['widgets'] ?? []],
            default => $this->dashboardReports->overview($filters),
        };
    }

    protected function runAttendanceReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'student_summary' => $this->attendanceReports->studentAttendancePercentage($filters),
            'class_summary' => $this->attendanceReports->classWiseSummaries($filters),
            'defaulters' => $this->attendanceReports->defaulters($filters),
            'staff_summary' => $this->attendanceReports->staffSummary($filters),
            default => $this->attendanceReports->overview($filters),
        };
    }

    protected function runFinanceReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'daily_collection' => $this->financeReports->dailyCollection($filters),
            'monthly_collection' => $this->financeReports->monthlyCollection($filters),
            'outstanding_dues' => $this->financeReports->outstandingDues($filters),
            'payment_methods' => $this->financeReports->paymentMethodSplit($filters),
            default => $this->financeReports->overview($filters),
        };
    }

    protected function runExamReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'subject_averages' => $this->examReports->subjectAverages($filters),
            'pass_fail' => $this->examReports->passFailRatios($filters),
            'toppers' => $this->examReports->toppers($filters),
            'trends' => $this->examReports->trends($filters),
            default => $this->examReports->overview($filters),
        };
    }

    protected function runTransportReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'route_students' => $this->transportReports->routeWiseStudents($filters),
            'vehicle_utilization' => $this->transportReports->vehicleUtilization($filters),
            default => $this->transportReports->overview($filters),
        };
    }

    protected function runCommunicationReport(?string $reportKey, array $filters): array
    {
        return match ($reportKey) {
            'delivery_rates' => $this->communicationReports->deliveryRates($filters),
            'channel_performance' => $this->communicationReports->channelPerformance($filters),
            'announcement_engagement' => $this->communicationReports->announcementEngagement($filters),
            'message_volume' => $this->communicationReports->messageVolume($filters),
            default => $this->communicationReports->overview($filters),
        };
    }

    protected function runCustomQuery(array $config, array $filters): array
    {
        $sourceTable = $config['source_table'] ?? null;
        if (! $sourceTable) {
            throw ValidationException::withMessages([
                'query_config' => ['Custom reports require a source_table in query_config.'],
            ]);
        }

        $allowedTables = [
            'attendance_summary' => 'attendance_summary',
            'student_results' => 'student_results',
            'notification_logs' => 'notification_logs',
            'finance_payments' => 'finance_payments',
            'finance_invoices' => 'finance_fee_invoices',
            'transport_trips' => 'transport_trips',
        ];

        if (! isset($allowedTables[$sourceTable])) {
            throw ValidationException::withMessages([
                'query_config' => ['The selected source_table is not allowed for custom reporting.'],
            ]);
        }

        $fields = $config['fields'] ?? ['id'];
        $groupBy = $config['group_by'] ?? [];
        $metrics = $config['metrics'] ?? [];
        $limit = (int) ($config['limit'] ?? 100);

        $query = DB::table($allowedTables[$sourceTable]);

        foreach (($config['filters'] ?? []) as $filter) {
            $field = $filter['field'] ?? null;
            $operator = strtolower((string) ($filter['operator'] ?? '='));
            $value = $filters[$field] ?? ($filter['value'] ?? null);

            if ($field === null || $value === null) {
                continue;
            }

            match ($operator) {
                '=', '>', '<', '>=', '<=', '!=' => $query->where($field, $operator, $value),
                'like' => $query->where($field, 'like', '%'.$value.'%'),
                'in' => $query->whereIn($field, (array) $value),
                default => null,
            };
        }

        $selects = [];

        foreach ($fields as $field) {
            $selects[] = $field;
        }

        foreach ($metrics as $metric) {
            $type = strtolower((string) ($metric['type'] ?? 'count'));
            $field = $metric['field'] ?? '*';
            $alias = $metric['alias'] ?? ($type.'_'.$field);

            $expression = match ($type) {
                'sum' => "SUM({$field}) as {$alias}",
                'avg' => "AVG({$field}) as {$alias}",
                'min' => "MIN({$field}) as {$alias}",
                'max' => "MAX({$field}) as {$alias}",
                default => "COUNT({$field}) as {$alias}",
            };

            $selects[] = DB::raw($expression);
        }

        $query->select($selects);

        if ($groupBy !== []) {
            $query->groupBy($groupBy);
        }

        foreach (($config['order_by'] ?? []) as $order) {
            $query->orderBy($order['field'] ?? 'id', $order['direction'] ?? 'asc');
        }

        $rows = $query->limit($limit)->get()->map(fn ($row) => (array) $row)->all();

        return [
            'summary' => [
                'rows_count' => count($rows),
                'source_table' => $sourceTable,
            ],
            'rows' => $rows,
        ];
    }
}
