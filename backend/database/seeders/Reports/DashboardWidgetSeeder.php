<?php

namespace Database\Seeders\Reports;

use App\Models\Reports\DashboardWidget;
use App\Models\School;
use Illuminate\Database\Seeder;

class DashboardWidgetSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $widgets = [
            [
                'name' => 'Total Students',
                'widget_type' => 'kpi',
                'module' => 'attendance',
                'config' => [
                    'metric_key' => 'total_students',
                    'report_key' => 'student_attendance_percentage',
                    'label' => 'Total Students',
                ],
                'position' => ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 2],
            ],
            [
                'name' => 'Today Attendance',
                'widget_type' => 'kpi',
                'module' => 'attendance',
                'config' => [
                    'metric_key' => 'today_present_students',
                    'report_key' => 'student_attendance_percentage',
                    'label' => 'Today Attendance',
                ],
                'position' => ['x' => 3, 'y' => 0, 'w' => 3, 'h' => 2],
            ],
            [
                'name' => 'Today Collection',
                'widget_type' => 'kpi',
                'module' => 'finance',
                'config' => [
                    'metric_key' => 'today_collection',
                    'report_key' => 'daily_collection',
                    'label' => 'Today Collection',
                ],
                'position' => ['x' => 6, 'y' => 0, 'w' => 3, 'h' => 2],
            ],
        ];

        foreach ($widgets as $widget) {
            DashboardWidget::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => $widget['name'],
                ],
                [
                    'widget_type' => $widget['widget_type'],
                    'module' => $widget['module'],
                    'config' => $widget['config'],
                    'position' => $widget['position'],
                    'is_system' => true,
                    'status' => 'active',
                ],
            );
        }
    }
}
