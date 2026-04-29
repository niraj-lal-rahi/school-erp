<?php

namespace App\Services\Reports;

use App\Models\Examination\Exam;
use App\Models\Student;
use App\Repositories\Contracts\Reports\DashboardRepositoryInterface;
use App\Models\Reports\DashboardWidget;
use App\Models\Reports\UserDashboardLayout;

class DashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboard,
        protected AttendanceReportService $attendanceReports,
        protected FinanceReportService $financeReports,
        protected ExamReportService $examReports,
        protected TransportReportService $transportReports,
        protected CommunicationReportService $communicationReports,
        protected ReportCacheService $cache,
    ) {
    }

    public function overview(array $filters = []): array
    {
        $schoolId = $filters['school_id'] ?? null;
        $cacheKey = $this->cache->buildKey('reports.dashboard.overview', $filters);

        return $schoolId
            ? $this->cache->remember($schoolId, $cacheKey, fn () => $this->computeOverview($filters), 600)
            : $this->computeOverview($filters);
    }

    public function widgets(?string $userType = null, ?int $userId = null, array $filters = []): array
    {
        $widgets = $this->dashboard->allWidgets([
            'module' => $filters['module'] ?? null,
            'status' => $filters['status'] ?? 'active',
        ]);

        $layout = ($userType && $userId)
            ? $this->dashboard->findLayout($userType, $userId)
            : null;

        return [
            'layout' => $layout?->layout ?? [],
            'widgets' => $widgets->map(fn (DashboardWidget $widget) => [
                'id' => $widget->id,
                'name' => $widget->name,
                'module' => $widget->module,
                'widget_type' => $widget->widget_type,
                'config' => $widget->config,
                'position' => $widget->position,
                'is_system' => $widget->is_system,
                'status' => $widget->status,
                'data' => $this->resolveWidgetData($widget, $filters),
            ])->values()->all(),
        ];
    }

    public function updateLayout(string $userType, int $userId, array $layout, int $schoolId): UserDashboardLayout
    {
        return $this->dashboard->upsertLayout($userType, $userId, $layout, $schoolId);
    }

    protected function computeOverview(array $filters): array
    {
        $today = $filters['date'] ?? now()->toDateString();
        $studentCount = Student::query()->count();
        $activeStudentCount = Student::query()->where('current_status', 'active')->count();
        $attendance = $this->attendanceReports->overview(array_merge($filters, [
            'date_from' => $today,
            'date_to' => $today,
        ]));
        $finance = $this->financeReports->dailyCollection(array_merge($filters, [
            'date_from' => $today,
            'date_to' => $today,
        ]));
        $communication = $this->communicationReports->deliveryRates(array_merge($filters, [
            'date_from' => $today,
            'date_to' => $today,
        ]));

        return [
            'kpis' => [
                'total_students' => $studentCount,
                'active_students' => $activeStudentCount,
                'today_present_students' => (int) ($attendance['student_summary']['present_count'] ?? 0),
                'today_absent_students' => (int) ($attendance['student_summary']['absent_count'] ?? 0),
                'today_collection' => (float) ($finance['rows'][0]['total_amount'] ?? 0),
                'published_exams' => Exam::query()->where('result_status', 'published')->count(),
                'notifications_sent_today' => (int) ($communication['summary']['sent_count'] ?? 0),
            ],
            'attendance' => $attendance,
            'finance' => $finance,
            'exam_overview' => $this->examReports->overview($filters),
            'transport_overview' => $this->transportReports->overview($filters),
            'communication_overview' => $this->communicationReports->overview($filters),
        ];
    }

    protected function resolveWidgetData(DashboardWidget $widget, array $filters = []): array
    {
        $config = $widget->config ?? [];

        return match ($widget->module) {
            'attendance' => match ($config['report_key'] ?? null) {
                'class_summary' => $this->attendanceReports->classWiseSummaries($filters),
                'defaulters' => $this->attendanceReports->defaulters($filters),
                'staff_summary' => $this->attendanceReports->staffSummary($filters),
                default => $this->attendanceReports->studentAttendancePercentage($filters),
            },
            'finance' => match ($config['report_key'] ?? null) {
                'daily_collection' => $this->financeReports->dailyCollection($filters),
                'monthly_collection' => $this->financeReports->monthlyCollection($filters),
                'outstanding' => $this->financeReports->outstandingDues($filters),
                default => $this->financeReports->paymentMethodSplit($filters),
            },
            'exams' => match ($config['report_key'] ?? null) {
                'subject_averages' => $this->examReports->subjectAverages($filters),
                'pass_fail' => $this->examReports->passFailRatios($filters),
                'toppers' => $this->examReports->toppers($filters),
                default => $this->examReports->trends($filters),
            },
            'transport' => match ($config['report_key'] ?? null) {
                'route_students' => $this->transportReports->routeWiseStudents($filters),
                default => $this->transportReports->vehicleUtilization($filters),
            },
            'communication' => match ($config['report_key'] ?? null) {
                'engagement' => $this->communicationReports->announcementEngagement($filters),
                'message_volume' => $this->communicationReports->messageVolume($filters),
                default => $this->communicationReports->channelPerformance($filters),
            },
            default => $this->overview($filters),
        };
    }
}
