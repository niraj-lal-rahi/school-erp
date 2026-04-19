<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('file_name');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            $table->string('issued_by')->nullable()->after('file_size');
            $table->date('issued_date')->nullable()->after('issued_by');
            $table->date('expiry_date')->nullable()->after('issued_date');
            $table->string('verification_status')->nullable()->after('expiry_date');
            $table->text('remarks')->nullable()->after('verification_status');
            $table->softDeletes();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'document_type']);
            $table->index(['school_id', 'verification_status']);
        });

        Schema::create('student_medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('blood_group', 8)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('medications')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_phone')->nullable();
            $table->string('hospital_name')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_medical_records');

        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'student_id']);
            $table->dropIndex(['school_id', 'document_type']);
            $table->dropIndex(['school_id', 'verification_status']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'file_name',
                'mime_type',
                'file_size',
                'issued_by',
                'issued_date',
                'expiry_date',
                'verification_status',
                'remarks',
            ]);
        });
    }
};
