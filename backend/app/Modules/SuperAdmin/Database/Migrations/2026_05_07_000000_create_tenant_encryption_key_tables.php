<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'platform';

    public function up(): void
    {
        Schema::connection($this->connection)->create('tenant_encryption_keys', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->string('key_reference', 191)->unique();
            $table->longText('encrypted_data_key')->nullable();
            $table->unsignedInteger('key_version')->default(1);
            $table->enum('status', ['pending', 'active', 'retired', 'revoked'])->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'key_version'], 'tenant_encryption_keys_tenant_version_unique');
            $table->index(['tenant_id', 'status'], 'tenant_encryption_keys_tenant_status_idx');
        });

        Schema::connection($this->connection)->create('tenant_key_rotation_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('platform_tenants')->cascadeOnDelete();
            $table->unsignedInteger('old_key_version');
            $table->unsignedInteger('new_key_version');
            $table->unsignedBigInteger('rotated_by')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status'], 'tenant_key_rotation_logs_tenant_status_idx');
            $table->index(['tenant_id', 'new_key_version'], 'tenant_key_rotation_logs_tenant_version_idx');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('tenant_key_rotation_logs');
        Schema::connection($this->connection)->dropIfExists('tenant_encryption_keys');
    }
};
