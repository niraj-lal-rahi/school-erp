<?php

namespace Database\Seeders\Auth;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $role = Role::withoutGlobalScopes()->where('school_id', $school->id)->where('slug', 'school-admin')->firstOrFail();

        $user = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'admin@greenwood.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'School',
                'last_name' => 'Admin',
                'name' => 'School Admin',
                'phone' => '9999999999',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->syncWithoutDetaching([
            $role->id => ['school_id' => $school->id],
        ]);
    }
}
