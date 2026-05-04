<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_fee_structures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'finance_fee_structures_school_code_unique');
            $table->index(['school_id', 'academic_year_id'], 'finance_fee_structures_school_year_idx');
            $table->index(['school_id', 'school_class_id'], 'finance_fee_structures_school_class_idx');
            $table->index(['school_id', 'section_id'], 'finance_fee_structures_school_section_idx');
            $table->index(['school_id', 'status'], 'finance_fee_structures_school_status_idx');
        });

        Schema::create('finance_fee_structure_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_structure_id')->constrained('finance_fee_structures')->cascadeOnDelete();
            $table->foreignId('fee_head_id')->constrained('finance_fee_heads')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('due_frequency', 30);
            $table->unsignedTinyInteger('due_day')->nullable();
            $table->unsignedSmallInteger('sort_order')->nullable();
            $table->timestamps();

            $table->unique(['fee_structure_id', 'fee_head_id'], 'finance_fee_structure_items_unique');
            $table->index(['school_id', 'fee_head_id'], 'finance_fee_structure_items_school_head_idx');
        });

        Schema::create('finance_student_fee_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('fee_structure_id')->constrained('finance_fee_structures')->cascadeOnDelete();
            $table->date('assigned_date');
            $table->string('status', 30)->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id'], 'finance_student_fee_assignments_school_student_idx');
            $table->index(['school_id', 'academic_year_id'], 'finance_student_fee_assignments_school_year_idx');
            $table->index(['school_id', 'school_class_id'], 'finance_student_fee_assignments_school_class_idx');
            $table->index(['school_id', 'section_id'], 'finance_student_fee_assignments_school_section_idx');
            $table->index(['school_id', 'status'], 'finance_student_fee_assignments_school_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_student_fee_assignments');
        Schema::dropIfExists('finance_fee_structure_items');
        Schema::dropIfExists('finance_fee_structures');
    }
};
