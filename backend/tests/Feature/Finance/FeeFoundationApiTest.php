<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeFoundationApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_authorized_user_can_manage_fee_categories_and_fee_heads(): void
    {
        $headers = $this->authenticate();

        $categoryResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/fee-categories', [
            'name' => 'Exam Fee',
            'code' => 'EXAM',
            'description' => 'Exam related finance category',
            'status' => 'active',
        ])->assertCreated();

        $categoryId = $categoryResponse->json('data.id');

        $this->withHeaders($headers)->postJson('/api/v1/finance/fee-heads', [
            'fee_category_id' => $categoryId,
            'name' => 'Final Exam Fee',
            'code' => 'FINAL-EXAM',
            'amount_type' => 'fixed',
            'default_amount' => 1500,
            'is_refundable' => false,
            'is_optional' => false,
            'status' => 'active',
        ])->assertCreated()->assertJsonPath('data.fee_category.id', $categoryId);

        $this->assertDatabaseHas('finance_fee_categories', ['code' => 'EXAM']);
        $this->assertDatabaseHas('finance_fee_heads', ['code' => 'FINAL-EXAM']);
    }

    public function test_fee_head_listing_supports_search_and_category_filters(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->getJson('/api/v1/finance/fee-categories');
        $tuitionCategoryId = collect($response->json('data'))->firstWhere('code', 'TUITION')['id'];

        $this->withHeaders($headers)->getJson("/api/v1/finance/fee-heads?search=Monthly&fee_category_id={$tuitionCategoryId}")
            ->assertOk()
            ->assertJsonFragment([
                'code' => 'MONTHLY-TUITION',
            ]);
    }
}
