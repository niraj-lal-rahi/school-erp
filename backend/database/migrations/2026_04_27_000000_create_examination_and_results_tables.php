<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'code'], 'exam_types_school_code_uq');
            $table->index(['school_id'], 'exam_types_school_ix');
            $table->index(['school_id', 'status'], 'exam_types_status_ix');
        });

        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('academic_year_id');
            $table->string('name');
            $table->string('code', 50);
            $table->unsignedBigInteger('exam_type_id');
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_marks', 10, 2)->nullable();
            $table->decimal('passing_marks', 10, 2)->nullable();
            $table->string('result_status', 20)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('academic_year_id')->references('id')->on('academic_years')->cascadeOnDelete();
            $table->foreign('exam_type_id')->references('id')->on('exam_types')->cascadeOnDelete();
            $table->foreign('term_id')->references('id')->on('academic_terms')->nullOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->nullOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['school_id', 'code'], 'exams_school_code_uq');
            $table->index(['school_id'], 'exams_school_ix');
            $table->index(['school_id', 'academic_year_id'], 'exams_year_ix');
            $table->index(['school_id', 'exam_type_id'], 'exams_type_ix');
            $table->index(['school_id', 'class_id', 'section_id'], 'exams_class_section_ix');
            $table->index(['school_id', 'result_status'], 'exams_result_status_ix');
            $table->index(['school_id', 'start_date', 'end_date'], 'exams_date_range_ix');
        });

        Schema::create('exam_subjects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('max_marks', 10, 2);
            $table->decimal('passing_marks', 10, 2)->nullable();
            $table->decimal('weightage', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();

            $table->unique(['exam_id', 'subject_id'], 'exam_subjects_exam_subject_uq');
            $table->index(['school_id'], 'exam_subjects_school_ix');
            $table->index(['school_id', 'exam_id'], 'exam_subjects_exam_ix');
            $table->index(['school_id', 'subject_id'], 'exam_subjects_subject_ix');
        });

        Schema::create('student_exam_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->string('roll_no', 50)->nullable();
            $table->string('status', 20)->default('enrolled');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('school_classes')->cascadeOnDelete();
            $table->foreign('section_id')->references('id')->on('sections')->nullOnDelete();

            $table->unique(['exam_id', 'student_id'], 'student_exam_enrollments_exam_student_uq');
            $table->index(['school_id'], 'student_exam_enrollments_school_ix');
            $table->index(['school_id', 'exam_id'], 'student_exam_enrollments_exam_ix');
            $table->index(['school_id', 'student_id'], 'student_exam_enrollments_student_ix');
            $table->index(['school_id', 'class_id', 'section_id'], 'student_exam_enrollments_class_sec_ix');
            $table->index(['school_id', 'status'], 'student_exam_enrollments_status_ix');
        });

        Schema::create('exam_marks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('marks_obtained', 10, 2)->nullable();
            $table->boolean('is_absent')->default(false);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('evaluated_by')->nullable();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->foreign('evaluated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['exam_id', 'student_id', 'subject_id'], 'exam_marks_exam_student_subject_uq');
            $table->index(['school_id'], 'exam_marks_school_ix');
            $table->index(['school_id', 'exam_id'], 'exam_marks_exam_ix');
            $table->index(['school_id', 'student_id'], 'exam_marks_student_ix');
            $table->index(['school_id', 'subject_id'], 'exam_marks_subject_ix');
            $table->index(['school_id', 'is_absent'], 'exam_marks_absent_ix');
        });

        Schema::create('grading_systems', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->string('name');
            $table->string('code', 50);
            $table->string('grading_type', 20);
            $table->decimal('pass_percentage', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->unique(['school_id', 'code'], 'grading_systems_school_code_uq');
            $table->index(['school_id'], 'grading_systems_school_ix');
            $table->index(['school_id', 'grading_type'], 'grading_systems_type_ix');
            $table->index(['school_id', 'status'], 'grading_systems_status_ix');
        });

        Schema::create('grade_scales', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('grading_system_id');
            $table->string('grade_label', 30);
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('grade_point', 6, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('grading_system_id')->references('id')->on('grading_systems')->cascadeOnDelete();

            $table->index(['school_id'], 'grade_scales_school_ix');
            $table->index(['school_id', 'grading_system_id'], 'grade_scales_system_ix');
            $table->index(['grading_system_id', 'min_percentage', 'max_percentage'], 'grade_scales_range_ix');
        });

        Schema::create('student_results', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->decimal('total_marks', 10, 2);
            $table->decimal('obtained_marks', 10, 2);
            $table->decimal('percentage', 6, 2);
            $table->string('grade', 30)->nullable();
            $table->decimal('gpa', 6, 2)->nullable();
            $table->string('result_status', 20)->default('pass');
            $table->unsignedInteger('rank')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();

            $table->unique(['exam_id', 'student_id'], 'student_results_exam_student_uq');
            $table->index(['school_id'], 'student_results_school_ix');
            $table->index(['school_id', 'exam_id'], 'student_results_exam_ix');
            $table->index(['school_id', 'student_id'], 'student_results_student_ix');
            $table->index(['school_id', 'result_status'], 'student_results_status_ix');
            $table->index(['school_id', 'rank'], 'student_results_rank_ix');
        });

        Schema::create('result_subject_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_result_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('max_marks', 10, 2);
            $table->decimal('obtained_marks', 10, 2);
            $table->string('grade', 30)->nullable();
            $table->boolean('is_pass')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('student_result_id')->references('id')->on('student_results')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();

            $table->unique(['student_result_id', 'subject_id'], 'result_subject_details_result_subj_uq');
            $table->index(['school_id'], 'result_subject_details_school_ix');
            $table->index(['school_id', 'student_result_id'], 'result_subject_details_result_ix');
            $table->index(['school_id', 'subject_id'], 'result_subject_details_subject_ix');
        });

        Schema::create('result_publications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('notify_users')->default(true);
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['school_id', 'exam_id'], 'result_publications_school_exam_uq');
            $table->index(['school_id'], 'result_publications_school_ix');
            $table->index(['school_id', 'published_at'], 'result_publications_published_ix');
            $table->index(['school_id', 'is_public'], 'result_publications_public_ix');
        });

        Schema::create('revaluation_requests', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('id')->on('schools')->cascadeOnDelete();
            $table->foreign('exam_id')->references('id')->on('exams')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();

            $table->index(['school_id'], 'revaluation_requests_school_ix');
            $table->index(['school_id', 'exam_id'], 'revaluation_requests_exam_ix');
            $table->index(['school_id', 'student_id'], 'revaluation_requests_student_ix');
            $table->index(['school_id', 'subject_id'], 'revaluation_requests_subject_ix');
            $table->index(['school_id', 'status'], 'revaluation_requests_status_ix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revaluation_requests');
        Schema::dropIfExists('result_publications');
        Schema::dropIfExists('result_subject_details');
        Schema::dropIfExists('student_results');
        Schema::dropIfExists('grade_scales');
        Schema::dropIfExists('grading_systems');
        Schema::dropIfExists('exam_marks');
        Schema::dropIfExists('student_exam_enrollments');
        Schema::dropIfExists('exam_subjects');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
    }
};
