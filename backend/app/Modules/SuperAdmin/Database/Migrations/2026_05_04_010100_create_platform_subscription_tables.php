<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'platform';

    public function up(): void
    {
        Schema::connection($this->connection)->create('subscription_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->unsignedInteger('max_students')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['status', 'created_at']);
        });

        Schema::connection($this->connection)->create('plan_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->string('feature_code');
            $table->string('feature_name');
            $table->string('module');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('limit_value')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'feature_code'], 'plan_feature_unique');
            $table->index(['subscription_plan_id', 'module']);
            $table->index(['module', 'is_enabled']);
        });

        Schema::connection($this->connection)->create('tenant_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->string('subscription_code')->unique();
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled', 'expired'])->default('trial');
            $table->boolean('auto_renew')->default(true);
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'billing_cycle']);
            $table->index(['tenant_id', 'next_billing_at']);
            $table->index(['subscription_plan_id', 'status']);
        });

        Schema::connection($this->connection)->create('tenant_billing_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('billing_date');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_reference')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'billing_date']);
            $table->index(['tenant_id', 'due_date']);
            $table->index(['subscription_id', 'status']);
        });

        Schema::connection($this->connection)->create('tenant_feature_access', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->string('feature_code');
            $table->string('module');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('limit_value')->nullable();
            $table->enum('access_source', ['plan', 'override', 'trial'])->default('plan');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'feature_code'], 'tenant_feature_unique');
            $table->index(['tenant_id', 'module']);
            $table->index(['tenant_id', 'is_enabled']);
            $table->index(['module', 'is_enabled']);
        });

        Schema::connection($this->connection)->create('platform_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('setting_group')->default('general');
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->enum('value_type', ['string', 'integer', 'boolean', 'json', 'encrypted', 'file'])->default('string');
            $table->boolean('is_sensitive')->default(false);
            $table->boolean('is_public')->default(false);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['setting_group', 'is_public']);
            $table->index(['is_sensitive', 'is_public']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('platform_settings');
        Schema::connection($this->connection)->dropIfExists('tenant_feature_access');
        Schema::connection($this->connection)->dropIfExists('tenant_billing_records');
        Schema::connection($this->connection)->dropIfExists('tenant_subscriptions');
        Schema::connection($this->connection)->dropIfExists('plan_features');
        Schema::connection($this->connection)->dropIfExists('subscription_plans');
    }
};
