<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('student_houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('code');
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('full_name')->nullable()->after('last_name');
            $table->string('roll_no')->nullable()->after('admission_no');
            $table->string('aadhaar_no')->nullable()->after('phone');
            $table->string('national_id')->nullable()->after('aadhaar_no');
            $table->string('religion')->nullable()->after('national_id');
            $table->foreignId('category_id')->nullable()->after('religion')->constrained('student_categories')->nullOnDelete();
            $table->foreignId('house_id')->nullable()->after('category_id')->constrained('student_houses')->nullOnDelete();
            $table->string('current_status')->default('active')->after('status');
            $table->date('joining_date')->nullable()->after('admission_date');
            $table->text('notes')->nullable()->after('medical_notes');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes();

            $table->index(['school_id', 'current_status']);
            $table->index(['school_id', 'category_id']);
            $table->index(['school_id', 'house_id']);
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('full_name')->nullable()->after('last_name');
            $table->string('alternate_phone')->nullable()->after('phone');
            $table->decimal('annual_income', 12, 2)->nullable()->after('occupation');
            $table->string('education')->nullable()->after('annual_income');
            $table->string('aadhaar_no')->nullable()->after('education');
            $table->string('national_id')->nullable()->after('aadhaar_no');
            $table->string('photo_path')->nullable()->after('national_id');
            $table->boolean('is_primary')->default(false)->after('photo_path');
            $table->boolean('can_receive_sms')->default(true)->after('is_primary');
            $table->boolean('can_receive_email')->default(true)->after('can_receive_sms');
            $table->boolean('can_pickup_student')->default(true)->after('can_receive_email');
            $table->string('address_line1')->nullable()->after('address');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->text('notes')->nullable()->after('postal_code');
            $table->string('status')->default('active')->after('notes');
            $table->softDeletes();

            $table->index(['school_id', 'status']);
        });

        Schema::table('student_guardian', function (Blueprint $table) {
            $table->string('relationship_label')->nullable()->after('relationship');
            $table->decimal('financial_responsibility_percentage', 5, 2)->nullable()->after('pickup_authorized');
            $table->text('notes')->nullable()->after('financial_responsibility_percentage');

            $table->index(['school_id', 'guardian_id']);
        });

        DB::table('students')->update([
            'full_name' => DB::raw("trim(coalesce(first_name, '') || ' ' || coalesce(middle_name, '') || ' ' || coalesce(last_name, ''))"),
            'current_status' => DB::raw("coalesce(status, 'active')"),
        ]);

        DB::table('guardians')->update([
            'full_name' => DB::raw("trim(coalesce(first_name, '') || ' ' || coalesce(middle_name, '') || ' ' || coalesce(last_name, ''))"),
        ]);

        DB::table('student_guardian')->update([
            'relationship_label' => DB::raw('relationship'),
        ]);
    }

    public function down(): void
    {
        $this->ensureIndexExists('student_guardian', 'student_guardian_school_id_tmp_idx', ['school_id']);
        $this->dropIndexIfExists('student_guardian', 'student_guardian_school_id_guardian_id_index');

        $this->dropColumnsIfExist('student_guardian', [
            'relationship_label',
            'financial_responsibility_percentage',
            'notes',
        ]);

        $this->ensureIndexExists('guardians', 'guardians_school_id_tmp_idx', ['school_id']);
        $this->dropIndexIfExists('guardians', 'guardians_school_id_status_index');

        Schema::table('guardians', function (Blueprint $table) {
            if (Schema::hasColumn('guardians', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        $this->dropColumnsIfExist('guardians', [
            'middle_name',
            'full_name',
            'alternate_phone',
            'annual_income',
            'education',
            'aadhaar_no',
            'national_id',
            'photo_path',
            'is_primary',
            'can_receive_sms',
            'can_receive_email',
            'can_pickup_student',
            'address_line1',
            'address_line2',
            'city',
            'state',
            'country',
            'postal_code',
            'notes',
            'status',
        ]);

        $this->ensureIndexExists('students', 'students_school_id_tmp_idx', ['school_id']);
        $this->dropIndexIfExists('students', 'students_school_id_current_status_index');
        $this->dropIndexIfExists('students', 'students_school_id_category_id_index');
        $this->dropIndexIfExists('students', 'students_school_id_house_id_index');

        Schema::table('students', function (Blueprint $table) {
            foreach (['created_by', 'updated_by', 'category_id', 'house_id'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            if (Schema::hasColumn('students', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        $this->dropColumnsIfExist('students', [
            'middle_name',
            'full_name',
            'roll_no',
            'aadhaar_no',
            'national_id',
            'religion',
            'current_status',
            'joining_date',
            'notes',
        ]);

        Schema::dropIfExists('student_houses');
        Schema::dropIfExists('student_categories');
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
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }

    protected function dropColumnsIfExist(string $tableName, array $columns): void
    {
        $existing = array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn($tableName, $column)));

        if ($existing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};
