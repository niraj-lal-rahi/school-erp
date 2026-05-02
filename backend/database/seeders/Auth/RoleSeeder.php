<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        Role::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => null,
                'code' => 'super_admin',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Super Administrator',
                'slug' => 'super_admin',
                'scope' => 'system',
                'description' => 'System-wide administrator with unrestricted access.',
                'role_type' => 'system',
                'is_default' => false,
                'status' => 'active',
            ]
        );

        $roles = [
            [
                'name' => 'Tenant Administrator',
                'code' => 'tenant_admin',
                'slug' => 'school-admin',
                'description' => 'Tenant administrator with full school access.',
                'is_default' => true,
            ],
            [
                'name' => 'Principal',
                'code' => 'principal',
                'slug' => 'principal',
                'description' => 'School principal with academic and reporting access.',
                'is_default' => false,
            ],
            [
                'name' => 'Teacher',
                'code' => 'teacher',
                'slug' => 'teacher',
                'description' => 'Teacher role for academic, attendance, communication, and exam workflows.',
                'is_default' => false,
            ],
            [
                'name' => 'Accountant',
                'code' => 'accountant',
                'slug' => 'accountant',
                'description' => 'Finance-focused role for fees and reporting.',
                'is_default' => false,
            ],
            [
                'name' => 'Receptionist',
                'code' => 'receptionist',
                'slug' => 'receptionist',
                'description' => 'Front-desk role for student and communication workflows.',
                'is_default' => false,
            ],
        ];

        foreach ($roles as $roleData) {
            Role::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'slug' => $roleData['slug'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $roleData['name'],
                    'code' => $roleData['code'],
                    'slug' => $roleData['slug'],
                    'scope' => 'tenant',
                    'description' => $roleData['description'],
                    'role_type' => 'tenant',
                    'is_default' => $roleData['is_default'],
                    'status' => 'active',
                ]
            );
        }
    }
}
