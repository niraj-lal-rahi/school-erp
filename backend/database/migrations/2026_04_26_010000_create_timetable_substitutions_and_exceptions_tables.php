<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_substitutions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('timetable_entry_id')->constrained('timetable_entries');
            $table->foreignId('original_staff_id')->constrained('staff');
            $table->foreignId('substitute_staff_id')->constrained('staff');
            $table->date('substitution_date');
            $table->text('reason')->nullable();
            $table->string('status', 30)->default('planned');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'timetable_entry_id', 'substitution_date'], 'tt_subs_entry_date_unique');
            $table->index(['school_id', 'substitute_staff_id', 'substitution_date'], 'tt_subs_staff_date_idx');
            $table->index(['school_id', 'original_staff_id', 'substitution_date'], 'tt_subs_orig_staff_date_idx');
            $table->index(['school_id', 'status'], 'tt_subs_status_idx');
        });

        Schema::create('timetable_schedule_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained('schools');
            $table->foreignId('academic_year_id')->constrained('academic_years');
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes');
            $table->foreignId('section_id')->nullable()->constrained('sections');
            $table->date('exception_date');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('exception_type', 50);
            $table->boolean('affects_attendance')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id', 'exception_date'], 'tt_exc_year_date_idx');
            $table->index(['school_id', 'school_class_id', 'section_id'], 'tt_exc_class_section_idx');
            $table->index(['school_id', 'exception_type'], 'tt_exc_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_schedule_exceptions');
        Schema::dropIfExists('timetable_substitutions');
    }
};
