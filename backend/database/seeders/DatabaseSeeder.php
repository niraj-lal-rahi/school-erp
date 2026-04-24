<?php

namespace Database\Seeders;

use Database\Seeders\Auth\PermissionSeeder;
use Database\Seeders\Auth\RoleSeeder;
use Database\Seeders\Auth\UserSeeder;
use Database\Seeders\AcademicManagement\AcademicManagementSeeder;
use Database\Seeders\HR\HrSeeder;
use Database\Seeders\SIS\SisSeeder;
use Database\Seeders\Tenant\TenantSeeder;
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
            SisSeeder::class,
        ]);
    }
}
