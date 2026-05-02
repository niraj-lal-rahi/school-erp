<?php

namespace Tests\Feature\Rbac;

use App\Models\Permission;
use App\Models\Role;
use App\Models\School;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(string $email = 'admin@greenwood.edu'): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', $email)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_tenant_admin_can_create_role(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->postJson('/api/v1/rbac/roles', [
            'name' => 'Admissions Coordinator',
            'code' => 'admissions_coordinator',
            'description' => 'Admission process coordination role.',
            'role_type' => 'tenant',
            'status' => 'active',
        ])->assertCreated();

        $this->assertDatabaseHas('roles', [
            'id' => $response->json('data.id'),
            'code' => 'admissions_coordinator',
            'school_id' => User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail()->school_id,
        ]);
    }

    public function test_tenant_admin_can_assign_permissions_to_role(): void
    {
        $headers = $this->authenticate();
        $role = Role::withoutGlobalScopes()->where('code', 'teacher')->firstOrFail();
        $permissionIds = Permission::query()
            ->whereIn('code', ['reports.view', 'reports.run'])
            ->pluck('id')
            ->all();

        $this->withHeaders($headers)->postJson("/api/v1/rbac/roles/{$role->id}/permissions", [
            'permission_ids' => $permissionIds,
        ])->assertOk();

        $this->assertEqualsCanonicalizing(
            $permissionIds,
            $role->fresh()->permissions()->pluck('permissions.id')->all()
        );
    }

    public function test_tenant_admin_can_assign_role_to_user(): void
    {
        $headers = $this->authenticate();
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $role = Role::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'teacher')->firstOrFail();
        $user = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $school->id,
            'first_name' => 'New',
            'last_name' => 'Teacher',
            'name' => 'New Teacher',
            'email' => 'new.teacher@greenwood.edu',
            'phone' => '9876500001',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/rbac/users/{$user->id}/roles", [
            'role_id' => $role->id,
        ])->assertCreated();

        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role_id' => $role->id,
            'school_id' => $school->id,
        ]);
    }

    public function test_permission_check_returns_true_for_allowed_permission(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->postJson('/api/v1/rbac/check-permission', [
            'permission_code' => 'rbac.manage',
        ])->assertOk()
            ->assertJsonPath('data.allowed', true);
    }

    public function test_tenant_isolation_blocks_assigning_other_tenant_role(): void
    {
        $headers = $this->authenticate();
        $greenwood = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $otherSchool = School::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Riverside School',
            'code' => 'riverside',
            'slug' => 'riverside-school',
            'domain' => 'riverside.local',
            'timezone' => 'Asia/Calcutta',
            'locale' => 'en',
            'status' => 'active',
            'settings' => ['currency' => 'INR'],
            'storage_disk' => 'local',
        ]);

        $crossTenantRole = Role::withoutGlobalScopes()->create([
            'school_id' => $otherSchool->id,
            'uuid' => (string) Str::uuid(),
            'name' => 'Other Tenant Role',
            'code' => 'other_tenant_role',
            'slug' => 'other-tenant-role',
            'scope' => 'tenant',
            'description' => 'Role belonging to another tenant.',
            'role_type' => 'tenant',
            'is_default' => false,
            'status' => 'active',
        ]);

        $targetUser = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $greenwood->id,
            'first_name' => 'Green',
            'last_name' => 'Target',
            'name' => 'Green Target',
            'email' => 'green.target@greenwood.edu',
            'phone' => '9876500002',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/rbac/users/{$targetUser->id}/roles", [
            'role_id' => $crossTenantRole->id,
        ])->assertStatus(422);
    }

    public function test_permission_middleware_blocks_user_without_rbac_access(): void
    {
        $this->seed();

        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $user = User::withoutGlobalScopes()->create([
            'uuid' => (string) Str::uuid(),
            'school_id' => $school->id,
            'first_name' => 'Limited',
            'last_name' => 'User',
            'name' => 'Limited User',
            'email' => 'limited.rbac@greenwood.edu',
            'phone' => '9876500003',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $token = app(JwtManager::class)->issueAccessToken($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->getJson('/api/v1/rbac/roles')->assertForbidden();
    }
}
