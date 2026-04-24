<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('payment_no');
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->nullable()->constrained('finance_fee_invoices')->nullOnDelete();
            $table->date('payment_date');
            $table->string('payment_method', 30);
            $table->string('gateway_provider')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('reference_no')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'payment_no'], 'finance_payments_school_no_unique');
            $table->index(['school_id', 'student_id'], 'finance_payments_school_student_idx');
            $table->index(['school_id', 'fee_invoice_id'], 'finance_payments_school_invoice_idx');
            $table->index(['school_id', 'payment_date'], 'finance_payments_school_date_idx');
            $table->index(['school_id', 'status'], 'finance_payments_school_status_idx');
        });

        Schema::create('finance_payment_allocations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained('finance_payments')->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('finance_fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_invoice_item_id')->nullable()->constrained('finance_fee_invoice_items')->nullOnDelete();
            $table->foreignId('fee_installment_id')->nullable()->constrained('finance_fee_installments')->nullOnDelete();
            $table->decimal('allocated_amount', 12, 2);
            $table->timestamps();

            $table->index(['school_id', 'payment_id'], 'finance_payment_alloc_school_pay_idx');
            $table->index(['school_id', 'fee_invoice_id'], 'finance_payment_alloc_school_inv_idx');
        });

        Schema::create('finance_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_no');
            $table->foreignId('payment_id')->constrained('finance_payments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('receipt_date');
            $table->decimal('amount', 12, 2);
            $table->string('receipt_pdf_path')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'receipt_no'], 'finance_receipts_school_no_unique');
            $table->unique(['payment_id'], 'finance_receipts_payment_unique');
            $table->index(['school_id', 'student_id'], 'finance_receipts_school_student_idx');
            $table->index(['school_id', 'receipt_date'], 'finance_receipts_school_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_receipts');
        Schema::dropIfExists('finance_payment_allocations');
        Schema::dropIfExists('finance_payments');
    }
};
