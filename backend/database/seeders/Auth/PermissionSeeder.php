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
