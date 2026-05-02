<?php

namespace Database\Seeders;

use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Auth\RoleSeeder;
use Database\Seeders\Auth\RolePermissionSeeder;
use Database\Seeders\Auth\UserSeeder;
use Database\Seeders\AcademicManagement\AcademicManagementSeeder;
use Database\Seeders\Attendance\AttendanceSeeder;
use Database\Seeders\Communication\CommunicationChannelSeeder;
use Database\Seeders\Communication\CommunicationDemoSeeder;
use Database\Seeders\Communication\CommunicationGroupSeeder;
use Database\Seeders\Communication\MessageTemplateSeeder;
use Database\Seeders\Examination\DemoExamSeeder;
use Database\Seeders\Examination\ExamTypeSeeder;
use Database\Seeders\Examination\GradingSystemSeeder;
use Database\Seeders\Finance\FinanceSeeder;
use Database\Seeders\HR\HrSeeder;
use Database\Seeders\Reports\DashboardWidgetSeeder;
use Database\Seeders\Reports\ReportDefinitionSeeder;
use Database\Seeders\Saas\DemoTenantSeeder;
use Database\Seeders\Saas\PlanFeatureSeeder;
use Database\Seeders\Saas\SubscriptionPlanSeeder;
use Database\Seeders\SIS\SisSeeder;
use Database\Seeders\Tenant\TenantSeeder;
use Database\Seeders\Timetable\TimetableSeeder;
use Database\Seeders\Transport\TransportSeeder;
use Database\Seeders\Portal\PortalDemoSeeder;
use Database\Seeders\Payments\DemoPaymentTransactionSeeder;
use Database\Seeders\Payments\PaymentGatewaySeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,
            SubscriptionPlanSeeder::class,
            PlanFeatureSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            DemoTenantSeeder::class,
            HrSeeder::class,
            AcademicManagementSeeder::class,
            TimetableSeeder::class,
            SisSeeder::class,
            FinanceSeeder::class,
            PaymentGatewaySeeder::class,
            DemoPaymentTransactionSeeder::class,
            AttendanceSeeder::class,
            TransportSeeder::class,
            CommunicationChannelSeeder::class,
            MessageTemplateSeeder::class,
            CommunicationGroupSeeder::class,
            CommunicationDemoSeeder::class,
            ExamTypeSeeder::class,
            GradingSystemSeeder::class,
            DemoExamSeeder::class,
            ReportDefinitionSeeder::class,
            DashboardWidgetSeeder::class,
            PortalDemoSeeder::class,
        ]);
    }
}
