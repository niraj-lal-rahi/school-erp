<?php

namespace Database\Seeders\Auth;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
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
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['code' => $permission['code']],
                [
                    'uuid' => (string) Str::uuid(),
                    ...$permission,
                    'description' => $permission['name'].' permission',
                ]
            );
        }
    }
}
