<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table): void {
            if (! Schema::hasColumn('academic_years', 'code')) {
                $table->string('code')->nullable()->after('name');
            }

            if (! Schema::hasColumn('academic_years', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('end_date');
            }

            if (! Schema::hasColumn('academic_years', 'status')) {
                $table->string('status')->default('draft')->after('is_active');
            }

            if (! Schema::hasColumn('academic_years', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('academic_years', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('academic_years', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['school_id', 'status'], 'academic_years_school_status_idx');
            $table->index(['school_id', 'is_active'], 'academic_years_school_active_idx');
        });

        DB::table('academic_years')
            ->where('is_current', true)
            ->update([
                'is_active' => true,
                'status' => 'active',
            ]);

        DB::table('academic_years')
            ->whereNull('code')
            ->orderBy('id')
            ->get()
            ->each(function (object $row): void {
                DB::table('academic_years')
                    ->where('id', $row->id)
                    ->update([
                        'code' => 'AY-'.$row->id,
                    ]);
            });

        Schema::table('academic_years', function (Blueprint $table): void {
            $table->unique(['school_id', 'code'], 'academic_years_school_code_unique');
        });

        Schema::table('school_classes', function (Blueprint $table): void {
            if (! Schema::hasColumn('school_classes', 'level_order')) {
                $table->unsignedSmallInteger('level_order')->default(0)->after('grade_level');
            }

            if (! Schema::hasColumn('school_classes', 'description')) {
                $table->text('description')->nullable()->after('sort_order');
            }

            if (! Schema::hasColumn('school_classes', 'status')) {
                $table->string('status')->default('active')->after('description');
            }

            if (! Schema::hasColumn('school_classes', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['school_id', 'academic_year_id'], 'school_classes_school_year_idx');
            $table->index(['school_id', 'status'], 'school_classes_school_status_idx');
        });

        Schema::table('sections', function (Blueprint $table): void {
            if (! Schema::hasColumn('sections', 'code')) {
                $table->string('code')->nullable()->after('name');
            }

            if (! Schema::hasColumn('sections', 'status')) {
                $table->string('status')->default('active')->after('capacity');
            }

            if (! Schema::hasColumn('sections', 'deleted_at')) {
                $table->softDeletes();
            }

            $table->index(['school_id', 'school_class_id'], 'sections_school_class_idx');
            $table->index(['school_id', 'status'], 'sections_school_status_idx');
        });

        DB::table('sections')
            ->whereNull('code')
            ->orderBy('id')
            ->get()
            ->each(function (object $row): void {
                DB::table('sections')
                    ->where('id', $row->id)
                    ->update([
                        'code' => 'SEC-'.$row->id,
                    ]);
            });

        Schema::table('sections', function (Blueprint $table): void {
            $table->unique(['school_id', 'school_class_id', 'code'], 'sections_school_class_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropUnique('sections_school_class_code_unique');
            $table->dropIndex('sections_school_class_idx');
            $table->dropIndex('sections_school_status_idx');

            if (Schema::hasColumn('sections', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            foreach (['status', 'code'] as $column) {
                if (Schema::hasColumn('sections', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('school_classes', function (Blueprint $table): void {
            $table->dropIndex('school_classes_school_year_idx');
            $table->dropIndex('school_classes_school_status_idx');

            if (Schema::hasColumn('school_classes', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            foreach (['status', 'description', 'level_order'] as $column) {
                if (Schema::hasColumn('school_classes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('academic_years', function (Blueprint $table): void {
            $table->dropUnique('academic_years_school_code_unique');
            $table->dropIndex('academic_years_school_status_idx');
            $table->dropIndex('academic_years_school_active_idx');

            foreach (['updated_by', 'created_by'] as $column) {
                if (Schema::hasColumn('academic_years', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            if (Schema::hasColumn('academic_years', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            foreach (['status', 'is_active', 'code'] as $column) {
                if (Schema::hasColumn('academic_years', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
