<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status')->nullable();
            $table->string('new_status');
            $table->string('action_type');
            $table->text('reason')->nullable();
            $table->date('effective_date');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'action_type']);
            $table->index(['school_id', 'new_status']);
        });

        Schema::create('student_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('note');
            $table->string('visibility_type')->default('internal');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'visibility_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_notes');
        Schema::dropIfExists('student_status_history');
    }
};
