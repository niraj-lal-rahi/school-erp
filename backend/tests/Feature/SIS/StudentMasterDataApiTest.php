<?php

namespace Tests\Feature\SIS;

use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentMasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function headers(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_user_can_manage_student_categories(): void
    {
        $headers = $this->headers();

        $createResponse = $this->withHeaders($headers)->postJson('/api/v1/student-categories', [
            'name' => 'Scholarship',
            'code' => 'SCH',
            'description' => 'Students on merit scholarship',
            'status' => 'active',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Scholarship')
            ->assertJsonPath('data.code', 'SCH');

        $categoryId = $createResponse->json('data.id');

        $this->withHeaders($headers)->putJson('/api/v1/student-categories/'.$categoryId, [
            'name' => 'Scholarship Students',
            'code' => 'SCH',
            'description' => 'Updated scholarship category',
            'status' => 'active',
        ])->assertOk()->assertJsonPath('data.name', 'Scholarship Students');

        $this->withHeaders($headers)->getJson('/api/v1/student-categories?search=Scholarship')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_manage_student_houses(): void
    {
        $headers = $this->headers();

        $createResponse = $this->withHeaders($headers)->postJson('/api/v1/student-houses', [
            'name' => 'Green House',
            'code' => 'GREEN',
            'color' => '#16a34a',
            'description' => 'Inter-house competition group',
            'status' => 'active',
        ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.name', 'Green House')
            ->assertJsonPath('data.color', '#16a34a');

        $houseId = $createResponse->json('data.id');

        $this->withHeaders($headers)->putJson('/api/v1/student-houses/'.$houseId, [
            'name' => 'Emerald House',
            'code' => 'GREEN',
            'color' => '#15803d',
            'description' => 'Updated house profile',
            'status' => 'active',
        ])->assertOk()->assertJsonPath('data.name', 'Emerald House');

        $this->withHeaders($headers)->getJson('/api/v1/student-houses?search=Emerald')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
