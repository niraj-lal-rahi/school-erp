<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_departments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('hr_designations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'code']);
            $table->index(['school_id', 'department_id']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('staff', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('hr_departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('hr_designations')->nullOnDelete();
            $table->string('employee_code');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name');
            $table->string('gender', 20);
            $table->date('date_of_birth')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('alternate_phone')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('staff_type')->default('teaching');
            $table->string('employment_type')->default('full_time');
            $table->date('joining_date');
            $table->date('leaving_date')->nullable();
            $table->string('current_status')->default('active');
            $table->string('qualification_summary')->nullable();
            $table->decimal('experience_years', 5, 2)->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['school_id', 'employee_code']);
            $table->unique(['school_id', 'email']);
            $table->index(['school_id', 'department_id']);
            $table->index(['school_id', 'designation_id']);
            $table->index(['school_id', 'staff_type']);
            $table->index(['school_id', 'employment_type']);
            $table->index(['school_id', 'current_status']);
        });

        $this->migrateAcademicStaffReferences();
    }

    public function down(): void
    {
        $this->rollbackAcademicStaffReferences();

        Schema::dropIfExists('staff');
        Schema::dropIfExists('hr_designations');
        Schema::dropIfExists('hr_departments');
    }

    protected function migrateAcademicStaffReferences(): void
    {
        if (! Schema::hasTable('teacher_assignments')) {
            return;
        }

        foreach (['teacher_assignments', 'lesson_plans', 'homework_assignments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['staff_id']);
            });
        }

        $userIds = collect(
            array_merge(
                DB::table('teacher_assignments')->pluck('staff_id')->filter()->all(),
                DB::table('lesson_plans')->pluck('staff_id')->filter()->all(),
                DB::table('homework_assignments')->pluck('staff_id')->filter()->all(),
            )
        )->unique()->values();

        $mapping = [];

        foreach ($userIds as $userId) {
            $user = DB::table('users')->where('id', $userId)->first();

            if (! $user) {
                continue;
            }

            $fullName = trim(implode(' ', array_filter([$user->name])));
            $staffId = DB::table('staff')->insertGetId([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'employee_code' => 'EMP-'.$user->school_id.'-'.$user->id,
                'first_name' => $user->name,
                'middle_name' => null,
                'last_name' => null,
                'full_name' => $fullName !== '' ? $fullName : 'Staff '.$user->id,
                'gender' => 'other',
                'email' => $user->email,
                'phone' => null,
                'alternate_phone' => null,
                'photo_path' => null,
                'staff_type' => 'teaching',
                'employment_type' => 'full_time',
                'joining_date' => now()->toDateString(),
                'leaving_date' => null,
                'current_status' => 'active',
                'qualification_summary' => null,
                'experience_years' => null,
                'address_line1' => null,
                'address_line2' => null,
                'city' => null,
                'state' => null,
                'country' => null,
                'postal_code' => null,
                'notes' => 'Auto-created during HR migration.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $mapping[$userId] = $staffId;
        }

        foreach (['teacher_assignments', 'lesson_plans', 'homework_assignments'] as $tableName) {
            foreach ($mapping as $legacyUserId => $staffId) {
                DB::table($tableName)->where('staff_id', $legacyUserId)->update(['staff_id' => $staffId]);
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnDelete();
            });
        }
    }

    protected function rollbackAcademicStaffReferences(): void
    {
        if (! Schema::hasTable('teacher_assignments')) {
            return;
        }

        $mapping = DB::table('staff')
            ->whereNotNull('user_id')
            ->pluck('user_id', 'id');

        foreach (['teacher_assignments', 'lesson_plans', 'homework_assignments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['staff_id']);
            });

            foreach ($mapping as $staffId => $userId) {
                DB::table($tableName)->where('staff_id', $staffId)->update(['staff_id' => $userId]);
            }

            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreign('staff_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
