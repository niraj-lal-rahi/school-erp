<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table): void {
            $table->foreignId('attendance_status_type_id')
                ->nullable()
                ->after('attendance_status')
                ->constrained('attendance_status_types')
                ->nullOnDelete();
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("
                UPDATE staff_attendance sa
                INNER JOIN attendance_status_types ast
                    ON ast.school_id = sa.school_id
                    AND ast.code = (
                        CASE sa.attendance_status
                            WHEN 'present' THEN 'PRESENT'
                            WHEN 'absent' THEN 'ABSENT'
                            WHEN 'late' THEN 'LATE'
                            WHEN 'half_day' THEN 'HALF_DAY'
                            WHEN 'leave' THEN 'LEAVE'
                            WHEN 'holiday' THEN 'HOLIDAY'
                            ELSE UPPER(sa.attendance_status)
                        END
                    )
                SET sa.attendance_status_type_id = ast.id
                WHERE sa.attendance_status_type_id IS NULL
            ");
        }

        Schema::table('staff_attendance', function (Blueprint $table): void {
            $table->index(['school_id', 'attendance_status_type_id'], 'staff_attendance_status_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table): void {
            $table->dropIndex('staff_attendance_status_type_idx');
            $table->dropConstrainedForeignId('attendance_status_type_id');
        });
    }
};
