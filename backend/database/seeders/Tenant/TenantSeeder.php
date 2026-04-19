<?php

namespace Database\Seeders\Tenant;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::updateOrCreate(
            ['code' => 'greenwood'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Greenwood International School',
                'slug' => 'greenwood-international-school',
                'domain' => 'greenwood.local',
                'timezone' => 'Asia/Calcutta',
                'locale' => 'en',
                'status' => 'active',
                'settings' => [
                    'currency' => 'INR',
                    'country' => 'IN',
                ],
                'storage_disk' => 's3',
            ]
        );

        AcademicYear::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'name' => '2026-2027',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_current' => true,
            ]
        );
    }
}
