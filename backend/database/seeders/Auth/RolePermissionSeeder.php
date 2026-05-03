<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $allPermissionIds = Permission::query()->pluck('id')->all();

        $rolePermissions = [
            'super_admin' => $allPermissionIds,
            'tenant_admin' => $allPermissionIds,
            'principal' => $this->permissionIdsForCodes([
                'academic-management.view',
                'academic-management.manage',
                'students.view',
                'attendance.view',
                'attendance.manage',
                'communication.view',
                'communication.manage',
                'exams.view',
                'exams.manage',
                'reports.view',
                'reports.run',
                'reports.export',
                'portal.view',
                'workflows.view',
                'workflows.approve',
                'documents.view',
                'documents.manage',
                'documents.verify',
            ]),
            'teacher' => $this->permissionIdsForCodes([
                'academic-management.view',
                'academic-management.manage',
                'students.view',
                'attendance.view',
                'attendance.manage',
                'timetable.view',
                'communication.view',
                'communication.manage',
                'exams.view',
                'exams.manage',
                'portal.view',
                'documents.view',
            ]),
            'accountant' => $this->permissionIdsForCodes([
                'students.view',
                'finance.view',
                'finance.manage',
                'communication.view',
                'reports.view',
                'reports.run',
                'reports.export',
                'workflows.view',
                'workflows.approve',
                'documents.view',
                'documents.verify',
            ]),
            'receptionist' => $this->permissionIdsForCodes([
                'students.view',
                'students.create',
                'students.update',
                'students.documents.upload',
                'communication.view',
                'communication.manage',
                'transport.view',
                'portal.view',
                'documents.view',
            ]),
        ];

        foreach ($rolePermissions as $code => $permissionIds) {
            $role = Role::withoutGlobalScopes()
                ->where('code', $code)
                ->when($code !== 'super_admin', fn ($query) => $query->where('school_id', $school->id))
                ->first();

            if (! $role) {
                continue;
            }

            $role->permissions()->sync($permissionIds);
        }
    }

    protected function permissionIdsForCodes(array $codes): array
    {
        return Permission::query()
            ->whereIn('code', $codes)
            ->pluck('id')
            ->all();
    }
}
