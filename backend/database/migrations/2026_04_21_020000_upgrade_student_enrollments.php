<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->nullable()->after('roll_number');
            $table->boolean('is_current')->default(true)->after('status');
            $table->text('remarks')->nullable()->after('is_current');
            $table->softDeletes();

            $table->index(['school_id', 'academic_year_id']);
            $table->index(['school_id', 'school_class_id']);
            $table->index(['school_id', 'section_id']);
            $table->index(['school_id', 'student_id', 'is_current']);
        });

        DB::table('student_enrollments')->update([
            'enrollment_date' => DB::raw('joined_on'),
        ]);

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->date('enrollment_date')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropIndex(['school_id', 'academic_year_id']);
            $table->dropIndex(['school_id', 'school_class_id']);
            $table->dropIndex(['school_id', 'section_id']);
            $table->dropIndex(['school_id', 'student_id', 'is_current']);
            $table->dropSoftDeletes();
            $table->dropColumn(['enrollment_date', 'is_current', 'remarks']);
        });
    }
};
