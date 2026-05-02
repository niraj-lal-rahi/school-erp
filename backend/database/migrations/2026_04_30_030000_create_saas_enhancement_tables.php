<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table): void {
            if (! Schema::hasColumn('schools', 'email')) {
                $table->string('email')->nullable()->after('code');
            }

            if (! Schema::hasColumn('schools', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (! Schema::hasColumn('schools', 'subdomain')) {
                $table->string('subdomain')->nullable()->unique()->after('domain');
            }

            if (! Schema::hasColumn('schools', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('subdomain');
            }

            if (! Schema::hasColumn('schools', 'address')) {
                $table->text('address')->nullable()->after('logo_path');
            }

            if (! Schema::hasColumn('schools', 'city')) {
                $table->string('city')->nullable()->after('address');
            }

            if (! Schema::hasColumn('schools', 'state')) {
                $table->string('state')->nullable()->after('city');
            }

            if (! Schema::hasColumn('schools', 'country')) {
                $table->string('country')->nullable()->after('state');
            }

            if (! Schema::hasColumn('schools', 'postal_code')) {
                $table->string('postal_code')->nullable()->after('country');
            }

            if (! Schema::hasColumn('schools', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('timezone');
            }

            if (! Schema::hasColumn('schools', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('schools', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('trial_ends_at');
            }

            if (! Schema::hasColumn('schools', 'suspended_at')) {
                $table->timestamp('suspended_at')->nullable()->after('activated_at');
            }

            if (! Schema::hasColumn('schools', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::create('subscription_plans', function (Blueprint $table): void {
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
        });

        Schema::create('plan_features', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->string('feature_code');
            $table->string('feature_name');
            $table->string('module');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('limit_value')->nullable();
            $table->timestamps();

            $table->unique(['subscription_plan_id', 'feature_code']);
            $table->index(['subscription_plan_id', 'module']);
        });

        Schema::create('tenant_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->enum('status', ['trial', 'active', 'past_due', 'suspended', 'cancelled', 'expired'])->default('trial');
            $table->boolean('auto_renew')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('subscription_plan_id');
            $table->index('status');
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'billing_cycle']);
        });

        Schema::create('tenant_usage_limits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('max_students')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_storage_mb')->nullable();
            $table->unsignedInteger('current_students')->default(0);
            $table->unsignedInteger('current_staff')->default(0);
            $table->unsignedInteger('current_storage_mb')->default(0);
            $table->timestamps();

            $table->unique('school_id');
            $table->index('subscription_plan_id');
        });

        Schema::create('tenant_feature_access', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('feature_code');
            $table->string('module');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('limit_value')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'feature_code']);
            $table->index(['school_id', 'module']);
        });

        Schema::create('tenant_billing_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('tenant_subscriptions')->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('INR');
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->date('billing_date');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index('subscription_id');
            $table->index('status');
        });

        Schema::create('tenant_domains', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->enum('domain_type', ['primary', 'custom', 'subdomain']);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('school_id');
            $table->index('status');
            $table->index('is_verified');
        });

        Schema::create('tenant_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('performed_by');
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_audit_logs');
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenant_billing_records');
        Schema::dropIfExists('tenant_feature_access');
        Schema::dropIfExists('tenant_usage_limits');
        Schema::dropIfExists('tenant_subscriptions');
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('subscription_plans');

        Schema::table('schools', function (Blueprint $table): void {
            if (Schema::hasColumn('schools', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            foreach ([
                'suspended_at',
                'activated_at',
                'trial_ends_at',
                'currency',
                'postal_code',
                'country',
                'state',
                'city',
                'address',
                'logo_path',
                'subdomain',
                'phone',
                'email',
            ] as $column) {
                if (Schema::hasColumn('schools', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
