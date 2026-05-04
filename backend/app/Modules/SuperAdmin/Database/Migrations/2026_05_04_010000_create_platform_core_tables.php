<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'platform';

    public function up(): void
    {
        Schema::connection($this->connection)->create('platform_tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index(['status', 'trial_ends_at']);
            $table->index(['status', 'activated_at']);
        });

        Schema::connection($this->connection)->create('tenant_database_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->string('connection_name');
            $table->string('database_name');
            $table->text('database_host');
            $table->text('database_port');
            $table->text('database_username');
            $table->longText('database_password');
            $table->string('database_driver', 30)->default('mysql');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_connected_at')->nullable();
            $table->enum('connection_status', ['unknown', 'connected', 'failed'])->default('unknown');
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedBigInteger('active_tenant_guard')->nullable()
                ->storedAs('case when `is_active` = 1 then `tenant_id` else null end');

            $table->unique(['tenant_id', 'connection_name'], 'tenant_connection_name_unique');
            $table->unique('active_tenant_guard', 'tenant_single_active_connection_unique');
            $table->index(['tenant_id', 'connection_status']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['database_name', 'is_active']);
        });

        Schema::connection($this->connection)->create('platform_admins', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('admin_type', ['super_admin', 'support_admin', 'billing_admin', 'security_admin']);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['user_id', 'admin_type'], 'platform_admin_user_type_unique');
            $table->index('status');
            $table->index(['admin_type', 'status']);
        });

        Schema::connection($this->connection)->create('tenant_security_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->boolean('encryption_enabled')->default(true);
            $table->boolean('database_isolated')->default(true);
            $table->boolean('emergency_access_enabled')->default(false);
            $table->boolean('backup_encryption_enabled')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique('tenant_id');
            $table->index('status');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenant_security_settings');
        Schema::connection($this->connection)->dropIfExists('platform_admins');
        Schema::connection($this->connection)->dropIfExists('tenant_database_connections');
        Schema::connection($this->connection)->dropIfExists('platform_tenants');
    }
};
