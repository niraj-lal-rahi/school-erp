<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_periods', function (Blueprint $table): void {
            $table->boolean('is_break')->default(false)->after('sequence');
            $table->string('break_type', 30)->nullable()->after('is_break');
            $table->softDeletes();
            $table->unique(['school_id', 'sequence'], 'attendance_periods_sequence_unique');
        });

        Schema::create('timetable_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->string('room_type', 40)->default('classroom');
            $table->unsignedInteger('capacity')->nullable();
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'timetable_rooms_code_unique');
            $table->index(['school_id', 'room_type'], 'timetable_rooms_type_idx');
            $table->index(['school_id', 'status'], 'timetable_rooms_status_idx');
        });

        Schema::create('timetable_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('status', 30)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code'], 'timetable_versions_code_unique');
            $table->index(['school_id', 'academic_year_id'], 'timetable_versions_year_idx');
            $table->index(['school_id', 'status'], 'timetable_versions_status_idx');
        });

        Schema::create('timetable_publish_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('timetable_version_id')->constrained('timetable_versions')->cascadeOnDelete();
            $table->string('action', 30);
            $table->foreignId('performed_by')->constrained('users')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'timetable_version_id'], 'timetable_publish_logs_version_idx');
            $table->index(['school_id', 'action'], 'timetable_publish_logs_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_publish_logs');
        Schema::dropIfExists('timetable_versions');
        Schema::dropIfExists('timetable_rooms');

        Schema::table('attendance_periods', function (Blueprint $table): void {
            $table->dropUnique('attendance_periods_sequence_unique');
            $table->dropColumn(['is_break', 'break_type', 'deleted_at']);
        });
    }
};
