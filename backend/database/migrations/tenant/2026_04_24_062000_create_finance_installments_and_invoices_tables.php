<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_fee_installments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_fee_assignment_id')->constrained('finance_student_fee_assignments')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained('finance_fee_heads')->cascadeOnDelete();
            $table->string('installment_name');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->string('status', 30)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_fee_assignment_id'], 'finance_installments_school_assign_idx');
            $table->index(['school_id', 'fee_head_id'], 'finance_installments_school_head_idx');
            $table->index(['school_id', 'due_date'], 'finance_installments_school_due_idx');
            $table->index(['school_id', 'status'], 'finance_installments_school_status_idx');
        });

        Schema::create('finance_fee_invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_no');
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->date('issue_date');
            $table->date('due_date');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('fine_total', 12, 2)->default(0);
            $table->decimal('tax_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2);
            $table->string('status', 30)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'invoice_no'], 'finance_invoices_school_no_unique');
            $table->index(['school_id', 'student_id'], 'finance_invoices_school_student_idx');
            $table->index(['school_id', 'academic_year_id'], 'finance_invoices_school_year_idx');
            $table->index(['school_id', 'due_date'], 'finance_invoices_school_due_idx');
            $table->index(['school_id', 'status'], 'finance_invoices_school_status_idx');
        });

        Schema::create('finance_fee_invoice_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_invoice_id')->constrained('finance_fee_invoices')->cascadeOnDelete();
            $table->foreignId('fee_installment_id')->nullable()->constrained('finance_fee_installments')->nullOnDelete();
            $table->foreignId('fee_head_id')->constrained('finance_fee_heads')->cascadeOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->timestamps();

            $table->index(['school_id', 'fee_invoice_id'], 'finance_invoice_items_school_invoice_idx');
            $table->index(['school_id', 'fee_installment_id'], 'finance_invoice_items_school_inst_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_fee_invoice_items');
        Schema::dropIfExists('finance_fee_invoices');
        Schema::dropIfExists('finance_fee_installments');
    }
};
