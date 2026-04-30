<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portal_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('profile_type', ['student', 'guardian']);
            $table->unsignedBigInteger('profile_id');
            $table->boolean('is_default')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['school_id', 'user_id', 'profile_type', 'profile_id'], 'portal_user_profiles_unique');
            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'profile_type', 'profile_id'], 'portal_user_profiles_profile_index');
            $table->index(['school_id', 'status']);
        });

        Schema::create('portal_profile_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('guardian_id')->nullable();
            $table->enum('access_type', ['self', 'parent', 'guardian']);
            $table->boolean('can_view_attendance')->default(true);
            $table->boolean('can_view_fees')->default(true);
            $table->boolean('can_pay_fees')->default(true);
            $table->boolean('can_view_results')->default(true);
            $table->boolean('can_view_documents')->default(true);
            $table->boolean('can_message_teacher')->default(true);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('guardian_id')->references('id')->on('guardians')->nullOnDelete();

            $table->unique(['school_id', 'user_id', 'student_id', 'access_type'], 'portal_profile_access_unique');
            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'guardian_id']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('portal_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('active_profile_type', ['student', 'guardian']);
            $table->unsignedBigInteger('active_profile_id');
            $table->unsignedBigInteger('active_student_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('device_info')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('active_student_id')->references('id')->on('students')->nullOnDelete();

            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'active_profile_type', 'active_profile_id'], 'portal_sessions_profile_index');
            $table->index(['school_id', 'active_student_id']);
            $table->index(['school_id', 'last_seen_at']);
        });

        Schema::create('portal_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->enum('notification_type', ['attendance', 'fee', 'result', 'announcement', 'transport', 'message', 'general']);
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();

            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'notification_type']);
            $table->index(['school_id', 'is_read']);
        });

        Schema::create('portal_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();

            $table->index(['school_id', 'user_id']);
            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'action']);
            $table->index(['school_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_activity_logs');
        Schema::dropIfExists('portal_notifications');
        Schema::dropIfExists('portal_sessions');
        Schema::dropIfExists('portal_profile_access');
        Schema::dropIfExists('portal_user_profiles');
    }
};
