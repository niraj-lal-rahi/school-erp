<?php

namespace Tests\Feature\Finance;

use App\Models\Finance\Expense;
use App\Models\Finance\ExpenseCategory;
use App\Models\Finance\LedgerAccount;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseLedgerApiTest extends TestCase
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

    public function test_authorized_user_can_manage_expense_categories_and_expenses(): void
    {
        $headers = $this->authenticate();

        $categoryResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/expense-categories', [
            'name' => 'Transport Expenses',
            'code' => 'TRANSPORT-EXP',
            'description' => 'Fuel and maintenance.',
            'status' => 'active',
        ])->assertCreated();

        $categoryId = $categoryResponse->json('data.id');

        $expenseResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/expenses', [
            'expense_category_id' => $categoryId,
            'title' => 'Diesel Purchase',
            'description' => 'Bus diesel refill.',
            'amount' => 3200,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'vendor_name' => 'Fuel Station',
            'reference_no' => 'DSL-001',
        ])->assertCreated();

        $expenseId = $expenseResponse->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/finance/expenses/{$expenseId}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->withHeaders($headers)->postJson("/api/v1/finance/expenses/{$expenseId}/mark-paid")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('finance_expenses', [
            'id' => $expenseId,
            'status' => 'paid',
        ]);
    }

    public function test_authorized_user_can_manage_ledger_accounts_and_entries(): void
    {
        $headers = $this->authenticate();

        $accountResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/ledger-accounts', [
            'name' => 'Cash In Hand',
            'code' => 'CASH-HAND',
            'account_type' => 'asset',
            'status' => 'active',
        ])->assertCreated();

        $accountId = $accountResponse->json('data.id');

        $entryResponse = $this->withHeaders($headers)->postJson('/api/v1/finance/ledger-entries', [
            'ledger_account_id' => $accountId,
            'source_type' => 'manual',
            'source_id' => 1,
            'entry_date' => now()->toDateString(),
            'debit' => 1500,
            'credit' => 0,
            'description' => 'Opening cash entry.',
        ])->assertCreated();

        $entryId = $entryResponse->json('data.id');

        $this->withHeaders($headers)->putJson("/api/v1/finance/ledger-entries/{$entryId}", [
            'ledger_account_id' => $accountId,
            'source_type' => 'manual',
            'source_id' => 1,
            'entry_date' => now()->toDateString(),
            'debit' => 2000,
            'credit' => 0,
            'description' => 'Updated opening cash entry.',
        ])->assertOk()->assertJsonPath('data.debit', '2000.00');

        $this->assertDatabaseHas('finance_ledger_entries', [
            'id' => $entryId,
            'source_type' => 'manual',
        ]);
    }

    public function test_ledger_entry_requires_only_one_side_of_amount(): void
    {
        $headers = $this->authenticate();
        $ledgerAccount = LedgerAccount::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/finance/ledger-entries', [
            'ledger_account_id' => $ledgerAccount->id,
            'source_type' => 'manual',
            'source_id' => 1,
            'entry_date' => now()->toDateString(),
            'debit' => 100,
            'credit' => 100,
            'description' => 'Invalid entry.',
        ])->assertUnprocessable();
    }
}
