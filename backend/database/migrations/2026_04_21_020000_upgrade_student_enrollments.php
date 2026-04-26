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
        $this->ensureIndexExists('student_enrollments', 'student_enrollments_school_id_tmp_idx', ['school_id']);
        $this->dropIndexIfExists('student_enrollments', 'student_enrollments_school_id_academic_year_id_index');
        $this->dropIndexIfExists('student_enrollments', 'student_enrollments_school_id_school_class_id_index');
        $this->dropIndexIfExists('student_enrollments', 'student_enrollments_school_id_section_id_index');
        $this->dropIndexIfExists('student_enrollments', 'student_enrollments_school_id_student_id_is_current_index');

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['enrollment_date', 'is_current', 'remarks']);
        });
    }

    protected function ensureIndexExists(string $tableName, string $indexName, array $columns): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    protected function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! $this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }

    protected function indexExists(string $tableName, string $indexName): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return false;
        }

        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
