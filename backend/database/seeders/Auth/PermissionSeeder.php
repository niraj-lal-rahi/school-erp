<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use App\Services\Rbac\PermissionSyncService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function __construct(
        protected PermissionSyncService $syncService,
    ) {
    }

    public function run(): void
    {
        $this->syncService->sync();

        $permissions = collect([
            ['name' => 'View Students', 'code' => 'students.view', 'module' => 'sis'],
            ['name' => 'Create Students', 'code' => 'students.create', 'module' => 'sis'],
            ['name' => 'Update Students', 'code' => 'students.update', 'module' => 'sis'],
            ['name' => 'Delete Students', 'code' => 'students.delete', 'module' => 'sis'],
            ['name' => 'Upload Student Documents', 'code' => 'students.documents.upload', 'module' => 'sis'],
            ['name' => 'Manage Student Medical Records', 'code' => 'students.medical.manage', 'module' => 'sis'],
            ['name' => 'View Academic Management', 'code' => 'academic-management.view', 'module' => 'academic-management'],
            ['name' => 'Manage Academic Management', 'code' => 'academic-management.manage', 'module' => 'academic-management'],
            ['name' => 'View HR', 'code' => 'hr.view', 'module' => 'hr'],
            ['name' => 'Manage HR', 'code' => 'hr.manage', 'module' => 'hr'],
            ['name' => 'View Finance', 'code' => 'finance.view', 'module' => 'finance'],
            ['name' => 'Manage Finance', 'code' => 'finance.manage', 'module' => 'finance'],
            ['name' => 'View Attendance', 'code' => 'attendance.view', 'module' => 'attendance'],
            ['name' => 'Manage Attendance', 'code' => 'attendance.manage', 'module' => 'attendance'],
            ['name' => 'View Timetable', 'code' => 'timetable.view', 'module' => 'timetable'],
            ['name' => 'Manage Timetable', 'code' => 'timetable.manage', 'module' => 'timetable'],
            ['name' => 'View Transport', 'code' => 'transport.view', 'module' => 'transport'],
            ['name' => 'Manage Transport', 'code' => 'transport.manage', 'module' => 'transport'],
            ['name' => 'View Communication', 'code' => 'communication.view', 'module' => 'communication'],
            ['name' => 'Manage Communication', 'code' => 'communication.manage', 'module' => 'communication'],
            ['name' => 'View Examinations', 'code' => 'exams.view', 'module' => 'examinations'],
            ['name' => 'Manage Examinations', 'code' => 'exams.manage', 'module' => 'examinations'],
            ['name' => 'View Reports', 'code' => 'reports.view', 'module' => 'reports'],
            ['name' => 'Manage Reports', 'code' => 'reports.manage', 'module' => 'reports'],
            ['name' => 'Run Reports', 'code' => 'reports.run', 'module' => 'reports'],
            ['name' => 'Export Reports', 'code' => 'reports.export', 'module' => 'reports'],
            ['name' => 'View Portal', 'code' => 'portal.view', 'module' => 'portal'],
            ['name' => 'Manage Portal', 'code' => 'portal.manage', 'module' => 'portal'],
            ['name' => 'Impersonate Portal', 'code' => 'portal.impersonate', 'module' => 'portal'],
            ['name' => 'View RBAC', 'code' => 'rbac.view', 'module' => 'rbac'],
            ['name' => 'Manage RBAC', 'code' => 'rbac.manage', 'module' => 'rbac'],
            ['name' => 'View Workflows', 'code' => 'workflows.view', 'module' => 'workflows'],
            ['name' => 'Manage Workflows', 'code' => 'workflows.manage', 'module' => 'workflows'],
            ['name' => 'Approve Workflows', 'code' => 'workflows.approve', 'module' => 'workflows'],
            ['name' => 'View Documents', 'code' => 'documents.view', 'module' => 'documents'],
            ['name' => 'Manage Documents', 'code' => 'documents.manage', 'module' => 'documents'],
            ['name' => 'Verify Documents', 'code' => 'documents.verify', 'module' => 'documents'],
            ['name' => 'View Settings', 'code' => 'settings.view', 'module' => 'settings'],
            ['name' => 'Manage Settings', 'code' => 'settings.manage', 'module' => 'settings'],
        ]);

        $permissions
            ->merge($this->legacyModulePermissions())
            ->unique('code')
            ->each(function (array $permission): void {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                [
                    'uuid' => (string) Str::uuid(),
                    ...$permission,
                    'description' => $permission['name'].' permission',
                    'action' => $permission['action'] ?? $this->deriveAction($permission['code']),
                    'is_system' => true,
                    'status' => 'active',
                ]
            );
            });
    }

    protected function legacyModulePermissions(): Collection
    {
        $modules = [
            'academic-management' => 'Academic Management',
            'communication' => 'Communication',
            'attendance' => 'Attendance',
            'finance' => 'Finance',
            'hr' => 'HR',
            'portal' => 'Portal',
            'reports' => 'Reports',
            'transport' => 'Transport',
            'timetable' => 'Timetable',
            'exams' => 'Examinations',
            'rbac' => 'RBAC',
            'workflows' => 'Workflows',
            'documents' => 'Documents',
            'settings' => 'Settings',
        ];

        return collect($modules)->flatMap(
            fn (string $name, string $module): array => [
                [
                    'name' => "View {$name}",
                    'code' => "{$module}.view",
                    'module' => $module,
                    'action' => 'view',
                ],
                [
                    'name' => "Manage {$name}",
                    'code' => "{$module}.manage",
                    'module' => $module,
                    'action' => 'manage',
                ],
            ]
        )->values();
    }

    protected function deriveAction(string $code): string
    {
        $segments = explode('.', $code);

        return end($segments) ?: 'view';
    }
}
