<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_definitions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->enum('module', ['admissions', 'fees', 'attendance', 'hr', 'exams', 'communication', 'transport', 'general']);
            $table->text('description')->nullable();
            $table->enum('trigger_type', ['event', 'schedule', 'manual']);
            $table->string('trigger_event')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'workflow_definitions_school_code_unique');
            $table->index(['school_id', 'module'], 'workflow_definitions_school_module_idx');
            $table->index(['school_id', 'status'], 'workflow_definitions_school_status_idx');
            $table->index(['school_id', 'trigger_type'], 'workflow_definitions_school_trigger_type_idx');
            $table->index(['school_id', 'trigger_event'], 'workflow_definitions_school_trigger_event_idx');
        });

        Schema::create('workflow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->string('step_name');
            $table->enum('step_type', ['approval', 'notification', 'condition', 'action', 'delay']);
            $table->unsignedInteger('sequence');
            $table->json('config')->nullable();
            $table->foreignId('assigned_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['workflow_definition_id', 'sequence'], 'workflow_steps_definition_sequence_unique');
            $table->index(['school_id', 'workflow_definition_id'], 'workflow_steps_school_definition_idx');
            $table->index(['school_id', 'step_type'], 'workflow_steps_school_type_idx');
            $table->index(['school_id', 'status'], 'workflow_steps_school_status_idx');
        });

        Schema::create('workflow_instances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workflow_definition_id')->constrained('workflow_definitions')->cascadeOnDelete();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('current_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'workflow_definition_id'], 'workflow_instances_school_definition_idx');
            $table->index(['school_id', 'status'], 'workflow_instances_school_status_idx');
            $table->index(['reference_type', 'reference_id'], 'workflow_instances_reference_idx');
            $table->index(['school_id', 'started_at'], 'workflow_instances_school_started_at_idx');
        });

        Schema::create('workflow_step_instances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workflow_instance_id')->constrained('workflow_instances')->cascadeOnDelete();
            $table->foreignId('workflow_step_id')->constrained('workflow_steps')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped', 'completed', 'failed'])->default('pending');
            $table->foreignId('action_taken_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('action_taken_at')->nullable();
            $table->text('remarks')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'workflow_instance_id'], 'workflow_step_instances_school_instance_idx');
            $table->index(['school_id', 'workflow_step_id'], 'workflow_step_instances_school_step_idx');
            $table->index(['school_id', 'status'], 'workflow_step_instances_school_status_idx');
            $table->index(['school_id', 'assigned_to'], 'workflow_step_instances_school_assigned_idx');
        });

        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->enum('module', ['fees', 'attendance', 'exams', 'communication', 'transport', 'hr', 'general']);
            $table->enum('trigger_type', ['event', 'schedule', 'condition']);
            $table->string('trigger_event')->nullable();
            $table->string('schedule_expression')->nullable();
            $table->json('conditions')->nullable();
            $table->json('actions');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'automation_rules_school_code_unique');
            $table->index(['school_id', 'module'], 'automation_rules_school_module_idx');
            $table->index(['school_id', 'status'], 'automation_rules_school_status_idx');
            $table->index(['school_id', 'trigger_type'], 'automation_rules_school_trigger_type_idx');
            $table->index(['school_id', 'trigger_event'], 'automation_rules_school_trigger_event_idx');
            $table->index(['school_id', 'next_run_at'], 'automation_rules_school_next_run_idx');
        });

        Schema::create('automation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('automation_rule_id')->constrained('automation_rules')->cascadeOnDelete();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('records_processed')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'automation_rule_id'], 'automation_runs_school_rule_idx');
            $table->index(['school_id', 'status'], 'automation_runs_school_status_idx');
            $table->index(['school_id', 'started_at'], 'automation_runs_school_started_idx');
            $table->index(['school_id', 'completed_at'], 'automation_runs_school_completed_idx');
        });

        Schema::create('automation_action_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('automation_run_id')->nullable()->constrained('automation_runs')->nullOnDelete();
            $table->foreignId('automation_rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
            $table->enum('action_type', ['email', 'sms', 'push', 'in_app', 'status_update', 'webhook', 'task', 'reminder']);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'automation_run_id'], 'automation_action_logs_school_run_idx');
            $table->index(['school_id', 'automation_rule_id'], 'automation_action_logs_school_rule_idx');
            $table->index(['school_id', 'action_type'], 'automation_action_logs_school_action_type_idx');
            $table->index(['school_id', 'status'], 'automation_action_logs_school_status_idx');
            $table->index(['reference_type', 'reference_id'], 'automation_action_logs_reference_idx');
        });

        Schema::create('approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('workflow_instance_id')->nullable()->constrained('workflow_instances')->nullOnDelete();
            $table->string('module');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approver_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'module'], 'approval_requests_school_module_idx');
            $table->index(['school_id', 'status'], 'approval_requests_school_status_idx');
            $table->index(['school_id', 'approver_id'], 'approval_requests_school_approver_idx');
            $table->index(['reference_type', 'reference_id'], 'approval_requests_reference_idx');
        });

        Schema::create('reminder_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->enum('module', ['fees', 'attendance', 'exams', 'transport', 'hr', 'general']);
            $table->enum('reminder_type', ['before_due', 'after_due', 'recurring', 'one_time']);
            $table->integer('offset_days')->nullable();
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly'])->default('once');
            $table->enum('channel', ['email', 'sms', 'push', 'in_app', 'multi']);
            $table->foreignId('template_id')->nullable()->constrained('message_templates')->nullOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'reminder_rules_school_code_unique');
            $table->index(['school_id', 'module'], 'reminder_rules_school_module_idx');
            $table->index(['school_id', 'status'], 'reminder_rules_school_status_idx');
            $table->index(['school_id', 'reminder_type'], 'reminder_rules_school_type_idx');
        });

        Schema::create('reminder_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('reminder_rule_id')->constrained('reminder_rules')->cascadeOnDelete();
            $table->enum('recipient_type', ['student', 'guardian', 'staff', 'user']);
            $table->unsignedBigInteger('recipient_id');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->enum('channel', ['email', 'sms', 'push', 'in_app', 'multi']);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'reminder_rule_id'], 'reminder_logs_school_rule_idx');
            $table->index(['school_id', 'recipient_type', 'recipient_id'], 'reminder_logs_school_recipient_idx');
            $table->index(['school_id', 'status'], 'reminder_logs_school_status_idx');
            $table->index(['reference_type', 'reference_id'], 'reminder_logs_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
        Schema::dropIfExists('reminder_rules');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('automation_action_logs');
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_rules');
        Schema::dropIfExists('workflow_step_instances');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_definitions');
    }
};
