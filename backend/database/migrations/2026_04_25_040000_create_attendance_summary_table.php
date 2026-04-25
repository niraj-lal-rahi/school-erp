<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_summary', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('user_type', 20);
            $table->unsignedBigInteger('user_id');
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->unsignedInteger('total_days')->default(0);
            $table->unsignedInteger('present_days')->default(0);
            $table->unsignedInteger('absent_days')->default(0);
            $table->unsignedInteger('leave_days')->default(0);
            $table->unsignedInteger('late_days')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'user_type', 'user_id', 'academic_year_id'], 'attendance_summary_unique');
            $table->index(['school_id', 'user_type', 'academic_year_id'], 'attendance_summary_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_summary');
    }
};
