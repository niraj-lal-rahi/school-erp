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
        Schema::table('admissions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
            $table->string('application_no')->nullable()->after('uuid');
            $table->foreignId('section_id')->nullable()->after('applied_class_id')->constrained('sections')->nullOnDelete();
            $table->string('first_name')->nullable()->after('section_id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_name');
            $table->string('gender', 20)->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('guardian_name')->nullable()->after('date_of_birth');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->string('guardian_email')->nullable()->after('guardian_phone');
            $table->string('address_line1')->nullable()->after('guardian_email');
            $table->string('address_line2')->nullable()->after('address_line1');
            $table->string('city')->nullable()->after('address_line2');
            $table->string('state')->nullable()->after('city');
            $table->string('country')->nullable()->after('state');
            $table->string('postal_code')->nullable()->after('country');
            $table->string('previous_school')->nullable()->after('postal_code');
            $table->string('application_status')->nullable()->after('previous_school');
            $table->timestamp('submitted_at')->nullable()->after('application_status');
            $table->foreignId('reviewed_by')->nullable()->after('submitted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->softDeletes();

            $table->index(['school_id', 'application_status']);
            $table->index(['school_id', 'academic_year_id']);
        });

        $rows = DB::table('admissions')->select(['id', 'school_id', 'student_id', 'status'])->get();

        foreach ($rows as $row) {
            $student = DB::table('students')->where('id', $row->student_id)->first();

            DB::table('admissions')->where('id', $row->id)->update([
                'uuid' => (string) Str::uuid(),
                'application_no' => 'APP-'.$row->school_id.'-'.$row->id,
                'first_name' => $student?->first_name,
                'last_name' => $student?->last_name,
                'gender' => $student?->gender,
                'date_of_birth' => $student?->date_of_birth,
                'guardian_name' => DB::table('guardians')
                    ->join('student_guardian', 'guardians.id', '=', 'student_guardian.guardian_id')
                    ->where('student_guardian.student_id', $row->student_id)
                    ->orderByDesc('student_guardian.is_primary')
                    ->value(DB::raw("coalesce(guardians.full_name, guardians.first_name || ' ' || coalesce(guardians.last_name, ''))")),
                'guardian_phone' => DB::table('guardians')
                    ->join('student_guardian', 'guardians.id', '=', 'student_guardian.guardian_id')
                    ->where('student_guardian.student_id', $row->student_id)
                    ->orderByDesc('student_guardian.is_primary')
                    ->value('guardians.phone'),
                'guardian_email' => DB::table('guardians')
                    ->join('student_guardian', 'guardians.id', '=', 'student_guardian.guardian_id')
                    ->where('student_guardian.student_id', $row->student_id)
                    ->orderByDesc('student_guardian.is_primary')
                    ->value('guardians.email'),
                'address_line1' => data_get(json_decode((string) ($student?->address ?? '{}'), true), 'line1'),
                'city' => data_get(json_decode((string) ($student?->address ?? '{}'), true), 'city'),
                'state' => data_get(json_decode((string) ($student?->address ?? '{}'), true), 'state'),
                'postal_code' => data_get(json_decode((string) ($student?->address ?? '{}'), true), 'postal_code'),
                'application_status' => $row->status === 'rejected' ? 'rejected' : 'converted',
            ]);
        }

        Schema::table('admissions', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
            $table->string('application_no')->nullable(false)->change();
            $table->string('application_status')->default('draft')->change();
            $table->unique(['school_id', 'application_no']);
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'application_no']);
            $table->dropIndex(['school_id', 'application_status']);
            $table->dropIndex(['school_id', 'academic_year_id']);
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropConstrainedForeignId('section_id');
            $table->dropSoftDeletes();
            $table->dropColumn([
                'uuid',
                'application_no',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'date_of_birth',
                'guardian_name',
                'guardian_phone',
                'guardian_email',
                'address_line1',
                'address_line2',
                'city',
                'state',
                'country',
                'postal_code',
                'previous_school',
                'application_status',
                'submitted_at',
                'reviewed_at',
            ]);
        });
    }
};
