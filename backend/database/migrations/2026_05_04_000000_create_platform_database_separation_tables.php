<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['status', 'slug']);
        });

        Schema::create('tenant_database_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->string('connection_name');
            $table->string('database_name');
            $table->text('database_host');
            $table->text('database_port');
            $table->text('database_username');
            $table->text('database_password');
            $table->string('database_driver')->default('mysql');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->enum('connection_status', ['unknown', 'connected', 'failed'])->default('unknown');
            $table->unsignedBigInteger('active_tenant_guard')->nullable()->storedAs('case when is_active = 1 then tenant_id else null end');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'connection_name']);
            $table->unique('active_tenant_guard');
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'connection_status']);
        });

        Schema::create('platform_admins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('admin_type', ['super_admin', 'support_admin', 'billing_admin', 'security_admin']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'admin_type']);
            $table->index('status');
        });

        Schema::create('platform_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('platform_tenants')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('module');
        });

        Schema::create('tenant_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->boolean('encryption_enabled')->default(true);
            $table->boolean('database_isolated')->default(true);
            $table->boolean('emergency_access_enabled')->default(false);
            $table->boolean('backup_encryption_enabled')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique('tenant_id');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_security_settings');
        Schema::dropIfExists('platform_audit_logs');
        Schema::dropIfExists('platform_admins');
        Schema::dropIfExists('tenant_database_connections');
        Schema::dropIfExists('platform_tenants');
    }
};
