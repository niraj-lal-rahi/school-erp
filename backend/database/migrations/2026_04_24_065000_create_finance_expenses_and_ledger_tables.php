<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_expense_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'fin_exp_cat_school_code_unq');
            $table->index(['school_id', 'status'], 'fin_exp_cat_school_status_idx');
        });

        Schema::create('finance_expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained('finance_expense_categories')->cascadeOnDelete();
            $table->string('expense_no');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('payment_method', 50)->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'expense_no'], 'fin_exp_school_no_unq');
            $table->index(['school_id', 'expense_category_id'], 'fin_exp_school_cat_idx');
            $table->index(['school_id', 'status'], 'fin_exp_school_status_idx');
            $table->index(['school_id', 'expense_date'], 'fin_exp_school_date_idx');
        });

        Schema::create('finance_ledger_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('account_type', 20);
            $table->foreignId('parent_id')->nullable()->constrained('finance_ledger_accounts')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'fin_led_acc_school_code_unq');
            $table->index(['school_id', 'account_type'], 'fin_led_acc_school_type_idx');
            $table->index(['school_id', 'status'], 'fin_led_acc_school_status_idx');
        });

        Schema::create('finance_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ledger_account_id')->constrained('finance_ledger_accounts')->cascadeOnDelete();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->date('entry_date');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'ledger_account_id'], 'fin_led_ent_school_acc_idx');
            $table->index(['school_id', 'source_type', 'source_id'], 'fin_led_ent_school_source_idx');
            $table->index(['school_id', 'entry_date'], 'fin_led_ent_school_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_ledger_entries');
        Schema::dropIfExists('finance_ledger_accounts');
        Schema::dropIfExists('finance_expenses');
        Schema::dropIfExists('finance_expense_categories');
    }
};
