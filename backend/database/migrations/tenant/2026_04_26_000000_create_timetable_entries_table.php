<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_version_id')->constrained('timetable_versions')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('day_of_week', 20);
            $table->foreignId('attendance_period_id')->constrained('attendance_periods')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('timetable_rooms')->nullOnDelete();
            $table->string('entry_type', 20)->default('class');
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['school_id', 'timetable_version_id', 'school_class_id', 'section_id', 'day_of_week', 'attendance_period_id'],
                'timetable_entries_slot_unique'
            );
            $table->index(['school_id', 'academic_year_id'], 'timetable_entries_year_idx');
            $table->index(['school_id', 'school_class_id', 'section_id'], 'timetable_entries_class_idx');
            $table->index(['school_id', 'staff_id', 'day_of_week'], 'timetable_entries_staff_idx');
            $table->index(['school_id', 'room_id', 'day_of_week'], 'timetable_entries_room_idx');
            $table->index(['school_id', 'status'], 'timetable_entries_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};
