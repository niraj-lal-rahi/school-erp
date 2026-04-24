<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_discount_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('discount_type', 20);
            $table->decimal('value', 12, 2);
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'finance_discount_types_school_code_unique');
            $table->index(['school_id', 'status'], 'finance_discount_types_school_status_idx');
        });

        Schema::create('finance_student_discounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_type_id')->constrained('finance_discount_types')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->nullable()->constrained('finance_fee_heads')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2);
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id'], 'finance_student_discounts_school_student_idx');
            $table->index(['school_id', 'academic_year_id'], 'finance_student_discounts_school_year_idx');
            $table->index(['school_id', 'status'], 'finance_student_discounts_school_status_idx');
        });

        Schema::create('finance_fine_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->foreignId('fee_head_id')->nullable()->constrained('finance_fee_heads')->nullOnDelete();
            $table->string('fine_type', 20);
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('grace_days')->default(0);
            $table->decimal('max_fine_amount', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'finance_fine_rules_school_code_unique');
            $table->index(['school_id', 'status'], 'finance_fine_rules_school_status_idx');
        });

        Schema::create('finance_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('refund_no');
            $table->foreignId('payment_id')->constrained('finance_payments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('refund_date');
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->string('status', 20)->default('requested');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'refund_no'], 'finance_refunds_school_no_unique');
            $table->index(['school_id', 'payment_id'], 'finance_refunds_school_payment_idx');
            $table->index(['school_id', 'student_id'], 'finance_refunds_school_student_idx');
            $table->index(['school_id', 'status'], 'finance_refunds_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_refunds');
        Schema::dropIfExists('finance_fine_rules');
        Schema::dropIfExists('finance_student_discounts');
        Schema::dropIfExists('finance_discount_types');
    }
};
