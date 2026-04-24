<?php

namespace Database\Seeders\Finance;

use App\Models\AcademicYear;
use App\Models\Finance\DiscountType;
use App\Models\Finance\Expense;
use App\Models\Finance\ExpenseCategory;
use App\Models\Finance\FeeCategory;
use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FineRule;
use App\Models\Finance\LedgerAccount;
use App\Models\Finance\LedgerEntry;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use App\Models\Finance\Refund;
use App\Models\Finance\StudentDiscount;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\Finance\FeeInstallmentService;
use App\Services\Finance\FeeInvoiceService;
use App\Services\Finance\PaymentService;
use Illuminate\Database\Seeder;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();

        $tuition = FeeCategory::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'TUITION'],
            [
                'name' => 'Tuition Fee',
                'description' => 'Core classroom tuition fee category.',
                'status' => 'active',
            ]
        );

        $transport = FeeCategory::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'TRANSPORT'],
            [
                'name' => 'Transport Fee',
                'description' => 'Bus and transport services.',
                'status' => 'active',
            ]
        );

        FeeHead::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'MONTHLY-TUITION'],
            [
                'fee_category_id' => $tuition->id,
                'name' => 'Monthly Tuition',
                'amount_type' => 'fixed',
                'default_amount' => 2500,
                'is_refundable' => false,
                'is_optional' => false,
                'status' => 'active',
            ]
        );

        FeeHead::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'BUS-ANNUAL'],
            [
                'fee_category_id' => $transport->id,
                'name' => 'Annual Bus Charge',
                'amount_type' => 'fixed',
                'default_amount' => 18000,
                'is_refundable' => false,
                'is_optional' => true,
                'status' => 'active',
            ]
        );

        $discountType = DiscountType::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'MERIT-10'],
            [
                'name' => 'Merit Scholarship',
                'discount_type' => 'percentage',
                'value' => 10,
                'max_amount' => 5000,
                'description' => 'Seeded merit-based fee discount.',
                'status' => 'active',
            ]
        );

        FineRule::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'LATE-FEE-DAILY'],
            [
                'name' => 'Late Fee Daily Rule',
                'fee_head_id' => null,
                'fine_type' => 'daily',
                'amount' => 25,
                'grace_days' => 3,
                'max_fine_amount' => 500,
                'status' => 'active',
            ]
        );

        $expenseCategory = ExpenseCategory::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'UTILITIES'],
            [
                'name' => 'Utilities',
                'description' => 'Electricity, water, internet, and related bills.',
                'status' => 'active',
            ]
        );

        $expenseLedger = LedgerAccount::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'EXP-UTIL'],
            [
                'name' => 'Utilities Expense',
                'account_type' => 'expense',
                'parent_id' => null,
                'status' => 'active',
            ]
        );

        $studentId = Student::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('id')->value('id');
        $currentEnrollment = $studentId
            ? StudentEnrollment::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('student_id', $studentId)
                ->where('is_current', true)
                ->latest('id')
                ->first()
            : null;
        $academicYearId = $currentEnrollment?->academic_year_id
            ?? AcademicYear::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('is_current', true)
                ->value('id');
        $schoolClassId = $currentEnrollment?->school_class_id
            ?? SchoolClass::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->orderBy('id')
                ->value('id');
        $sectionId = $currentEnrollment?->section_id
            ?? Section::withoutGlobalScopes()
                ->where('school_id', $school->id)
                ->where('school_class_id', $schoolClassId)
                ->orderBy('id')
                ->value('id');
        $monthlyTuition = FeeHead::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'MONTHLY-TUITION')->firstOrFail();
        $busAnnual = FeeHead::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'BUS-ANNUAL')->firstOrFail();

        $feeStructure = FeeStructure::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'GRADE-FOUNDATION'],
            [
                'academic_year_id' => $academicYearId,
                'school_class_id' => $schoolClassId,
                'section_id' => null,
                'name' => 'Foundation Grade Fee Structure',
                'description' => 'Seeded structure for current students in the first class.',
                'effective_from' => now()->startOfMonth()->toDateString(),
                'effective_to' => null,
                'status' => 'active',
            ]
        );

        $feeStructure->items()->delete();
        $feeStructure->items()->createMany([
            [
                'school_id' => $school->id,
                'fee_head_id' => $monthlyTuition->id,
                'amount' => 2500,
                'due_frequency' => 'monthly',
                'due_day' => 5,
                'sort_order' => 1,
            ],
            [
                'school_id' => $school->id,
                'fee_head_id' => $busAnnual->id,
                'amount' => 18000,
                'due_frequency' => 'yearly',
                'due_day' => 10,
                'sort_order' => 2,
            ],
        ]);

        if ($studentId && $academicYearId && $schoolClassId) {
            $assignment = StudentFeeAssignment::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'fee_structure_id' => $feeStructure->id,
                ],
                [
                    'school_class_id' => $schoolClassId,
                    'section_id' => $sectionId,
                    'assigned_date' => now()->toDateString(),
                    'status' => 'active',
                    'remarks' => 'Seeded starter assignment.',
                ]
            );

            if (! FeeInstallment::withoutGlobalScopes()->where('student_fee_assignment_id', $assignment->id)->exists()) {
                app(FeeInstallmentService::class)->generateForAssignment($assignment);
            }

            $installmentIds = FeeInstallment::withoutGlobalScopes()
                ->where('student_fee_assignment_id', $assignment->id)
                ->orderBy('due_date')
                ->limit(2)
                ->pluck('id')
                ->all();

            if ($installmentIds !== [] && ! FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->where('student_id', $studentId)->exists()) {
                app(FeeInvoiceService::class)->create(\App\DataTransferObjects\Finance\FeeInvoiceData::fromArray([
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'academic_year_id' => $academicYearId,
                    'issue_date' => now()->toDateString(),
                    'due_date' => now()->addDays(10)->toDateString(),
                    'notes' => 'Seeded starter invoice.',
                    'installment_ids' => $installmentIds,
                    'created_by' => null,
                ]));
            }

            $invoice = FeeInvoice::withoutGlobalScopes()->where('school_id', $school->id)->where('student_id', $studentId)->first();
            if ($invoice && ! Payment::withoutGlobalScopes()->where('school_id', $school->id)->where('fee_invoice_id', $invoice->id)->exists()) {
                app(PaymentService::class)->collect(\App\DataTransferObjects\Finance\PaymentData::fromArray([
                    'school_id' => $school->id,
                    'student_id' => $studentId,
                    'fee_invoice_id' => $invoice->id,
                    'payment_date' => now()->toDateString(),
                    'payment_method' => 'cash',
                    'amount' => min(2500, (float) $invoice->balance_amount),
                    'remarks' => 'Seeded starter payment.',
                    'received_by' => null,
                    'allocations' => [[
                        'fee_invoice_id' => $invoice->id,
                        'fee_installment_id' => FeeInstallment::withoutGlobalScopes()
                            ->where('student_fee_assignment_id', $assignment->id)
                            ->orderBy('due_date')
                            ->value('id'),
                        'allocated_amount' => min(2500, (float) $invoice->balance_amount),
                    ]],
                ]));
            }

            $payment = Payment::withoutGlobalScopes()->where('school_id', $school->id)->where('fee_invoice_id', $invoice?->id)->first();
            if ($payment && ! Receipt::withoutGlobalScopes()->where('payment_id', $payment->id)->exists()) {
                app(\App\Services\Finance\ReceiptService::class)->generateForPayment($payment);
            }

            if ($studentId) {
                StudentDiscount::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'student_id' => $studentId,
                        'academic_year_id' => $academicYearId,
                        'discount_type_id' => $discountType->id,
                    ],
                    [
                        'fee_head_id' => $monthlyTuition->id,
                        'discount_amount' => 500,
                        'reason' => 'Seeded scholarship record.',
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]
                );
            }

            if ($payment) {
                Refund::withoutGlobalScopes()->updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'payment_id' => $payment->id,
                    ],
                    [
                        'refund_no' => 'RFD-SEED-0001',
                        'student_id' => $studentId,
                        'refund_date' => now()->toDateString(),
                        'amount' => min(200, (float) $payment->amount),
                        'reason' => 'Seeded refund request.',
                        'status' => 'requested',
                    ]
                );
            }

            $expense = Expense::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'expense_no' => 'EXP-SEED-0001',
                ],
                [
                    'expense_category_id' => $expenseCategory->id,
                    'title' => 'Monthly Electricity Bill',
                    'description' => 'Seeded utilities expense.',
                    'amount' => 4200,
                    'expense_date' => now()->subDays(3)->toDateString(),
                    'payment_method' => 'bank_transfer',
                    'vendor_name' => 'City Power Ltd.',
                    'reference_no' => 'UTIL-APR-001',
                    'status' => 'approved',
                    'created_by' => 1,
                    'approved_by' => 1,
                    'approved_at' => now()->subDays(2),
                ]
            );

            LedgerEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'ledger_account_id' => $expenseLedger->id,
                    'source_type' => Expense::class,
                    'source_id' => $expense->id,
                ],
                [
                    'entry_date' => now()->subDays(2)->toDateString(),
                    'debit' => 4200,
                    'credit' => 0,
                    'description' => 'Seeded utilities ledger entry.',
                ]
            );
        }
    }
}
