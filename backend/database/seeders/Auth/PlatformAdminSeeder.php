<?php

namespace Database\Seeders\Auth;

use App\Models\Platform\PlatformAdmin;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformAdminSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::withoutGlobalScopes()
            ->whereNull('school_id')
            ->where(function ($query): void {
                $query->where('code', 'super_admin')
                    ->orWhere('slug', 'super_admin');
            })
            ->firstOrFail();

        $user = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => null,
                'email' => 'superadmin@system.local',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'name' => 'Super Admin',
                'phone' => '9000000099',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $superAdminRole->id => ['school_id' => null],
        ]);

        if (Schema::connection('platform')->hasTable('platform_admins')) {
            PlatformAdmin::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'admin_type' => 'super_admin',
                    'status' => 'active',
                ]
            );
        }
    }
}
