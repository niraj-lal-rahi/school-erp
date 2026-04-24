<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_fee_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'finance_fee_categories_school_code_unique');
            $table->index(['school_id', 'status'], 'finance_fee_categories_school_status_idx');
        });

        Schema::create('finance_fee_heads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_category_id')->constrained('finance_fee_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->string('amount_type', 20);
            $table->decimal('default_amount', 12, 2)->nullable();
            $table->boolean('is_refundable')->default(false);
            $table->boolean('is_optional')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'finance_fee_heads_school_code_unique');
            $table->index(['school_id', 'fee_category_id'], 'finance_fee_heads_school_category_idx');
            $table->index(['school_id', 'status'], 'finance_fee_heads_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_fee_heads');
        Schema::dropIfExists('finance_fee_categories');
    }
};
