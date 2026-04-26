<?php

namespace Database\Seeders;

use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Auth\RoleSeeder;
use Database\Seeders\Auth\UserSeeder;
use Database\Seeders\AcademicManagement\AcademicManagementSeeder;
use Database\Seeders\Attendance\AttendanceSeeder;
use Database\Seeders\Communication\CommunicationChannelSeeder;
use Database\Seeders\Communication\CommunicationDemoSeeder;
use Database\Seeders\Communication\CommunicationGroupSeeder;
use Database\Seeders\Communication\MessageTemplateSeeder;
use Database\Seeders\Finance\FinanceSeeder;
use Database\Seeders\HR\HrSeeder;
use Database\Seeders\SIS\SisSeeder;
use Database\Seeders\Tenant\TenantSeeder;
use Database\Seeders\Timetable\TimetableSeeder;
use Database\Seeders\Transport\TransportSeeder;
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
            PermissionSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            HrSeeder::class,
            AcademicManagementSeeder::class,
            TimetableSeeder::class,
            SisSeeder::class,
            FinanceSeeder::class,
            AttendanceSeeder::class,
            TransportSeeder::class,
            CommunicationChannelSeeder::class,
            MessageTemplateSeeder::class,
            CommunicationGroupSeeder::class,
            CommunicationDemoSeeder::class,
        ]);
    }
}
