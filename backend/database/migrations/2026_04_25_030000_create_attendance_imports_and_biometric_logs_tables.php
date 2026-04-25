<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('import_type', 20);
            $table->date('import_date');
            $table->string('status', 30)->default('pending');
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->longText('logs')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'import_type', 'status'], 'attendance_imports_type_status_idx');
            $table->index(['school_id', 'import_date'], 'attendance_imports_date_idx');
        });

        Schema::create('attendance_biometric_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('device_id')->nullable();
            $table->string('user_type', 20);
            $table->unsignedBigInteger('user_id');
            $table->dateTime('log_datetime');
            $table->string('log_type', 20);
            $table->longText('raw_data')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();

            $table->index(['school_id', 'user_type', 'user_id'], 'attendance_biometric_logs_user_idx');
            $table->index(['school_id', 'processed'], 'attendance_biometric_logs_processed_idx');
            $table->index(['school_id', 'log_datetime'], 'attendance_biometric_logs_datetime_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_biometric_logs');
        Schema::dropIfExists('attendance_imports');
    }
};
