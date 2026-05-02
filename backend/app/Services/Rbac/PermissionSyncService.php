<?php

namespace App\Services\Rbac;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissionSyncService
{
    public const MODULES = [
        'academic',
        'student',
        'hr',
        'finance',
        'attendance',
        'timetable',
        'transport',
        'communication',
        'exams',
        'reports',
        'portal',
        'rbac',
    ];

    public const ACTIONS = [
        'view',
        'manage',
        'create',
        'update',
        'delete',
        'approve',
        'publish',
        'export',
    ];

    public function sync(?array $map = null): Collection
    {
        $permissionMap = $map ?: $this->defaultPermissionMap();
        $synced = collect();

        foreach ($permissionMap as $module => $actions) {
            PermissionGroup::query()->updateOrCreate(
                ['code' => $module],
                [
                    'name' => Str::headline($module).' Permissions',
                    'module' => $module,
                    'description' => Str::headline($module).' module permissions.',
                    'status' => 'active',
                ]
            );

            foreach ($actions as $action) {
                $code = "{$module}.{$action}";
                $name = Str::headline($action).' '.Str::headline($module);

                $permission = Permission::query()->updateOrCreate(
                    ['code' => $code],
                    [
                        'uuid' => (string) Str::uuid(),
                        'name' => $name,
                        'module' => $module,
                        'action' => $action,
                        'description' => "{$name} permission",
                        'is_system' => true,
                        'status' => 'active',
                    ]
                );

                $synced->push($permission);
            }
        }

        return $synced->unique('id')->values();
    }

    protected function defaultPermissionMap(): array
    {
        return collect(self::MODULES)
            ->mapWithKeys(fn (string $module) => [$module => self::ACTIONS])
            ->all();
    }
}
