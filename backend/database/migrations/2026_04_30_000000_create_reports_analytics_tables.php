<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_definitions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 80);
            $table->string('module', 30);
            $table->text('description')->nullable();
            $table->json('query_config');
            $table->json('default_filters')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['school_id'], 'report_definitions_school_ix');
            $table->index(['school_id', 'module'], 'report_definitions_module_ix');
            $table->index(['school_id', 'status'], 'report_definitions_status_ix');
            $table->index(['school_id', 'is_system'], 'report_definitions_system_ix');
        });

        Schema::create('report_schedules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('report_definition_id');
            $table->string('schedule_type', 20);
            $table->json('schedule_config');
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('channel', 20);
            $table->json('recipients');
            $table->string('status', 20)->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('report_definition_id')->references('id')->on('report_definitions')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['school_id'], 'report_schedules_school_ix');
            $table->index(['school_id', 'report_definition_id'], 'report_schedules_definition_ix');
            $table->index(['school_id', 'status'], 'report_schedules_status_ix');
            $table->index(['school_id', 'next_run_at'], 'report_schedules_next_run_ix');
            $table->index(['report_definition_id', 'status', 'next_run_at'], 'report_schedules_run_lookup_ix');
        });

        Schema::create('report_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('report_definition_id');
            $table->string('run_type', 20);
            $table->string('status', 20)->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type', 10)->default('json');
            $table->json('parameters')->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('report_definition_id')->references('id')->on('report_definitions')->cascadeOnDelete();
            $table->foreign('initiated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['school_id'], 'report_runs_school_ix');
            $table->index(['school_id', 'report_definition_id'], 'report_runs_definition_ix');
            $table->index(['school_id', 'status'], 'report_runs_status_ix');
            $table->index(['started_at'], 'report_runs_started_at_ix');
            $table->index(['completed_at'], 'report_runs_completed_at_ix');
        });

        Schema::create('dashboard_widgets', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('widget_type', 20);
            $table->string('module', 30);
            $table->json('config');
            $table->json('position');
            $table->boolean('is_system')->default(false);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();

            $table->index(['school_id'], 'dashboard_widgets_school_ix');
            $table->index(['school_id', 'module'], 'dashboard_widgets_module_ix');
            $table->index(['school_id', 'widget_type'], 'dashboard_widgets_type_ix');
            $table->index(['school_id', 'status'], 'dashboard_widgets_status_ix');
            $table->index(['school_id', 'is_system'], 'dashboard_widgets_system_ix');
        });

        Schema::create('user_dashboard_layouts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('user_type', 20);
            $table->unsignedBigInteger('user_id');
            $table->json('layout');
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();

            $table->index(['school_id'], 'user_dashboard_layouts_school_ix');
            $table->index(['school_id', 'user_type', 'user_id'], 'user_dashboard_layouts_user_ix');
            $table->unique(['school_id', 'user_type', 'user_id'], 'user_dashboard_layouts_unique');
        });

        Schema::create('report_cache', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('cache_key', 191);
            $table->longText('data');
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();

            $table->unique(['school_id', 'cache_key'], 'report_cache_school_key_uq');
            $table->index(['school_id'], 'report_cache_school_ix');
            $table->index(['school_id', 'expires_at'], 'report_cache_expires_ix');
        });

        Schema::create('report_exports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('report_run_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedInteger('downloaded_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('report_run_id')->references('id')->on('report_runs')->cascadeOnDelete();

            $table->index(['school_id'], 'report_exports_school_ix');
            $table->index(['school_id', 'report_run_id'], 'report_exports_run_ix');
            $table->index(['school_id', 'last_downloaded_at'], 'report_exports_downloaded_ix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('report_cache');
        Schema::dropIfExists('user_dashboard_layouts');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('report_runs');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('report_definitions');
    }
};
