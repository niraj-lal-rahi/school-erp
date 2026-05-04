<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_terms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedTinyInteger('sequence')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'code']);
            $table->index(['school_id', 'academic_year_id', 'status']);
        });

        Schema::create('subjects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code');
            $table->string('type')->default('mandatory');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('class_subject_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_optional')->default(false);
            $table->unsignedTinyInteger('weekly_periods')->default(1);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id'], 'class_subject_assignments_unique');
            $table->index(['school_id', 'academic_year_id', 'school_class_id', 'status'], 'class_subject_assignments_filter_idx');
        });

        Schema::create('teacher_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_class_teacher')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id'], 'teacher_assignments_unique');
            $table->index(['school_id', 'staff_id', 'status'], 'teacher_assignments_staff_idx');
        });

        Schema::create('curricula', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('sequence')->default(1);
            $table->text('learning_outcomes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'school_class_id', 'subject_id'], 'curricula_filter_idx');
        });

        Schema::create('lesson_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('topic');
            $table->text('objectives');
            $table->string('teaching_method')->nullable();
            $table->date('planned_date');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('materials_needed')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id'], 'lesson_plans_filter_idx');
        });

        Schema::create('homework_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_term_id')->nullable()->constrained('academic_terms')->nullOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->date('assigned_date');
            $table->date('due_date');
            $table->decimal('total_marks', 8, 2)->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'school_class_id', 'section_id', 'subject_id', 'staff_id'], 'homework_assignments_filter_idx');
        });

        Schema::create('academic_calendar_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->boolean('is_holiday')->default(false);
            $table->string('audience_type')->default('all');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'event_type', 'status'], 'academic_calendar_events_filter_idx');
        });

        Schema::create('grading_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('pass_percentage', 5, 2)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'academic_year_id', 'name']);
            $table->index(['school_id', 'academic_year_id', 'status'], 'grading_structures_filter_idx');
        });

        Schema::create('grade_scale_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grading_structure_id')->constrained()->cascadeOnDelete();
            $table->string('grade_label');
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 5, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['grading_structure_id', 'min_percentage', 'max_percentage'], 'grade_scale_items_filter_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scale_items');
        Schema::dropIfExists('grading_structures');
        Schema::dropIfExists('academic_calendar_events');
        Schema::dropIfExists('homework_assignments');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('curricula');
        Schema::dropIfExists('teacher_assignments');
        Schema::dropIfExists('class_subject_assignments');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('academic_terms');
    }
};
