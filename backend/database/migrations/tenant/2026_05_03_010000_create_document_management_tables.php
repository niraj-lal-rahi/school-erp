<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->enum('applies_to', ['student', 'staff', 'tenant', 'finance', 'academic', 'general']);
            $table->boolean('requires_verification')->default(false);
            $table->boolean('has_expiry')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'document_categories_school_id_code_unique');
            $table->index(['school_id', 'applies_to', 'status'], 'document_categories_school_applies_status_index');
        });

        Schema::create('document_folders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->enum('visibility', ['private', 'internal', 'shared'])->default('private');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'document_folders_school_id_code_unique');
            $table->index(['school_id', 'parent_id', 'visibility'], 'document_folders_school_parent_visibility_index');
        });

        Schema::create('documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->nullOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('document_folders')->nullOnDelete();
            $table->enum('owner_type', ['student', 'staff', 'guardian', 'tenant', 'user', 'general'])->default('general');
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('document_no')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->enum('status', ['active', 'archived', 'deleted'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('document_no');
            $table->index(['school_id', 'owner_type', 'owner_id'], 'documents_school_owner_index');
            $table->index(['school_id', 'category_id'], 'documents_school_category_index');
            $table->index(['school_id', 'folder_id'], 'documents_school_folder_index');
            $table->index(['school_id', 'verification_status'], 'documents_school_verification_index');
            $table->index(['school_id', 'expiry_date'], 'documents_school_expiry_index');
            $table->index(['owner_type', 'owner_id'], 'documents_owner_type_owner_id_index');
        });

        Schema::create('document_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('version_no')->default(1);
            $table->string('file_name');
            $table->string('original_file_name');
            $table->string('file_path');
            $table->enum('disk', ['local', 'public', 's3'])->default('local');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_current')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['document_id', 'is_current'], 'document_files_document_current_index');
            $table->index(['school_id', 'document_id', 'version_no'], 'document_files_school_document_version_index');
        });

        Schema::create('document_permissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->enum('permission_type', ['user', 'role', 'owner']);
            $table->unsignedBigInteger('permission_id')->nullable();
            $table->boolean('can_view')->default(true);
            $table->boolean('can_download')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_verify')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'document_id'], 'document_permissions_school_document_index');
            $table->index(['permission_type', 'permission_id'], 'document_permissions_type_id_index');
        });

        Schema::create('document_verifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'document_id', 'status'], 'document_verifications_school_document_status_index');
        });

        Schema::create('document_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->timestamps();

            $table->index(['school_id', 'code'], 'document_tags_school_code_index');
        });

        Schema::create('document_tag_mappings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('document_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'tag_id'], 'document_tag_mappings_document_id_tag_id_unique');
            $table->index(['school_id', 'tag_id'], 'document_tag_mappings_school_tag_index');
        });

        Schema::create('document_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->enum('action', ['uploaded', 'viewed', 'downloaded', 'updated', 'deleted', 'restored', 'verified', 'rejected', 'permission_changed']);
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'document_id', 'action'], 'document_audit_logs_school_document_action_index');
        });

        Schema::create('document_bulk_uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->enum('upload_type', ['student', 'staff', 'general']);
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->longText('error_log')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'upload_type', 'status'], 'document_bulk_uploads_school_type_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_bulk_uploads');
        Schema::dropIfExists('document_audit_logs');
        Schema::dropIfExists('document_tag_mappings');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('document_verifications');
        Schema::dropIfExists('document_permissions');
        Schema::dropIfExists('document_files');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_folders');
        Schema::dropIfExists('document_categories');
    }
};
