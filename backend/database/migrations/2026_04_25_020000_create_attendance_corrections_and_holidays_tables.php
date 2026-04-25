<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_corrections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('reference_type', 20);
            $table->unsignedBigInteger('reference_id');
            $table->date('attendance_date');
            $table->foreignId('old_status_id')->nullable()->constrained('attendance_status_types')->nullOnDelete();
            $table->foreignId('new_status_id')->constrained('attendance_status_types')->restrictOnDelete();
            $table->text('reason');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('review_remarks')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'reference_type', 'reference_id'], 'attendance_corrections_reference_idx');
            $table->index(['school_id', 'attendance_date'], 'attendance_corrections_date_idx');
            $table->index(['school_id', 'status'], 'attendance_corrections_status_idx');
        });

        Schema::create('attendance_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('applies_to', 20)->default('all');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'academic_year_id', 'start_date', 'end_date'], 'attendance_holidays_date_idx');
            $table->index(['school_id', 'applies_to'], 'attendance_holidays_applies_idx');
            $table->index(['school_id', 'school_class_id', 'section_id'], 'attendance_holidays_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_holidays');
        Schema::dropIfExists('attendance_corrections');
    }
};
