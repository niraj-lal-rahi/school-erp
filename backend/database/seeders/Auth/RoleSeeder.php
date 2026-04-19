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

        $role = Role::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'slug' => 'school-admin',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'School Administrator',
                'scope' => 'tenant',
                'description' => 'Tenant administrator with full SIS access.',
            ]
        );

        $role->permissions()->sync(Permission::query()->pluck('id'));
    }
}
