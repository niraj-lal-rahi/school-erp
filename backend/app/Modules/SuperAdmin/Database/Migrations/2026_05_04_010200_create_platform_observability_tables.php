<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'platform';

    public function up(): void
    {
        Schema::connection($this->connection)->create('platform_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('platform_tenants')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('module')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['module', 'created_at']);
        });

        Schema::connection($this->connection)->create('system_health_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('platform_tenants')->nullOnDelete();
            $table->string('component');
            $table->string('check_name');
            $table->enum('status', ['healthy', 'warning', 'failed'])->default('healthy');
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->timestamps();

            $table->index(['component', 'status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['checked_at', 'status']);
        });

        Schema::connection($this->connection)->create('emergency_access_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('platform_tenants')->nullOnDelete();
            $table->unsignedBigInteger('platform_admin_user_id')->nullable();
            $table->unsignedBigInteger('approved_by_user_id')->nullable();
            $table->enum('action', ['enabled', 'disabled', 'granted', 'revoked', 'used'])->default('enabled');
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'action']);
            $table->index(['platform_admin_user_id', 'created_at']);
            $table->index('expires_at');
        });

        Schema::connection($this->connection)->create('tenant_impersonation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('platform_admin_user_id');
            $table->unsignedBigInteger('impersonated_user_id')->nullable();
            $table->enum('status', ['started', 'ended', 'revoked', 'failed'])->default('started');
            $table->text('reason')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['platform_admin_user_id', 'status']);
            $table->index(['started_at', 'status']);
        });

        Schema::connection($this->connection)->create('tenant_backup_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->enum('backup_type', ['full', 'incremental', 'manual', 'emergency'])->default('full');
            $table->string('storage_disk')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->boolean('is_encrypted')->default(true);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->longText('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'backup_type']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenant_backup_logs');
        Schema::connection($this->connection)->dropIfExists('tenant_impersonation_logs');
        Schema::connection($this->connection)->dropIfExists('emergency_access_logs');
        Schema::connection($this->connection)->dropIfExists('system_health_logs');
        Schema::connection($this->connection)->dropIfExists('platform_audit_logs');
    }
};
