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
