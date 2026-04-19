<?php

namespace Tests\Feature\Tenant;

use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fall_back_to_their_own_tenant_context(): void
    {
        $this->seed();
        $user = \App\Models\User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/sis/students')
            ->assertOk();
    }
}
