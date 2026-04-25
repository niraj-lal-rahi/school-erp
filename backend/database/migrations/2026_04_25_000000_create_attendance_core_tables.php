<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_status_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->boolean('is_present')->default(false);
            $table->boolean('counts_for_attendance')->default(false);
            $table->string('color_code', 20)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'attendance_status_types_code_unique');
            $table->index(['school_id', 'status'], 'attendance_status_types_status_idx');
        });

        Schema::create('attendance_periods', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('status', 30)->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'code'], 'attendance_periods_code_unique');
            $table->index(['school_id', 'status'], 'attendance_periods_status_idx');
            $table->index(['school_id', 'sequence'], 'attendance_periods_sequence_idx');
        });

        Schema::create('attendance_student_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('session_type', 20);
            $table->foreignId('attendance_period_id')->nullable()->constrained('attendance_periods')->nullOnDelete();
            $table->string('session_slot', 60);
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('status', 30)->default('draft');
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['school_id', 'academic_year_id', 'school_class_id', 'section_id', 'attendance_date', 'session_slot'],
                'attendance_student_sessions_unique'
            );
            $table->index(['school_id', 'attendance_date'], 'attendance_student_sessions_date_idx');
            $table->index(['school_id', 'academic_year_id', 'school_class_id', 'section_id'], 'attendance_student_sessions_scope_idx');
            $table->index(['school_id', 'teacher_id'], 'attendance_student_sessions_teacher_idx');
        });

        Schema::create('attendance_student_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_session_id')->constrained('attendance_student_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_status_type_id')->constrained('attendance_status_types')->restrictOnDelete();
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['attendance_session_id', 'student_id'], 'attendance_student_records_unique');
            $table->index(['school_id', 'student_id'], 'attendance_student_records_student_idx');
            $table->index(['school_id', 'attendance_status_type_id'], 'attendance_student_records_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_student_records');
        Schema::dropIfExists('attendance_student_sessions');
        Schema::dropIfExists('attendance_periods');
        Schema::dropIfExists('attendance_status_types');
    }
};
