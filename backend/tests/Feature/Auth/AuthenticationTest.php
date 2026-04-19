<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_admin_can_login_and_fetch_profile(): void
    {
        $this->seed();

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'tenant_code' => 'greenwood',
            'email' => 'admin@greenwood.edu',
            'password' => 'password123',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('data.user.email', 'admin@greenwood.edu')
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'refresh_token',
                    'tenant',
                ],
            ]);

        $token = $loginResponse->json('data.access_token');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@greenwood.edu');
    }
}
