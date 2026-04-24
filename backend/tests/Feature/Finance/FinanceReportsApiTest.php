<?php

namespace Tests\Feature\Finance;

use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceReportsApiTest extends TestCase
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

    public function test_fee_collection_report_returns_summary_and_breakdown(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)
            ->getJson('/api/v1/finance/reports/fee-collection')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['total_collected', 'payments_count'],
                    'by_method',
                ],
            ]);
    }

    public function test_outstanding_fees_and_daily_collection_reports_return_data(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)
            ->getJson('/api/v1/finance/reports/outstanding-fees')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['outstanding_total', 'students_with_dues', 'invoices_count'],
                    'top_outstanding_students',
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/finance/reports/daily-collection')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['days_count', 'average_daily_collection'],
                    'rows',
                ],
            ]);
    }

    public function test_student_ledger_and_income_vs_expense_reports_return_expected_shapes(): void
    {
        $headers = $this->authenticate();
        $student = Student::withoutGlobalScopes()->firstOrFail();

        $this->withHeaders($headers)
            ->getJson("/api/v1/finance/reports/student-ledger?student_id={$student->id}")
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['total_invoiced', 'total_paid', 'closing_balance'],
                    'entries',
                ],
            ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/finance/reports/income-vs-expense')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['income_total', 'expense_total', 'net_surplus'],
                    'breakdown',
                ],
            ]);
    }

    public function test_expense_summary_report_returns_category_breakdown(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)
            ->getJson('/api/v1/finance/reports/expense-summary')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => ['total_expenses', 'expenses_count'],
                    'by_category',
                ],
            ]);
    }
}
