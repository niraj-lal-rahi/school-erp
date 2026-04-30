<?php

namespace Database\Seeders\Portal;

use App\Models\AcademicYear;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Examination\StudentResult;
use App\Models\Finance\FeeInvoice;
use App\Models\Guardian;
use App\Models\Permission;
use App\Models\Portal\PortalNotification;
use App\Models\Portal\PortalProfileAccess;
use App\Models\Portal\PortalSession;
use App\Models\Portal\PortalUserProfile;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PortalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->orderBy('id')
            ->firstOrFail();
        $section = Section::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('school_class_id', $class->id)
            ->orderBy('id')
            ->firstOrFail();
        $admin = User::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('email', 'admin@greenwood.edu')
            ->firstOrFail();

        $portalRole = Role::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'slug' => 'portal-user',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Portal User',
                'scope' => 'tenant',
                'description' => 'Portal access for students and guardians.',
            ]
        );

        $portalPermissions = Permission::query()
            ->whereIn('code', ['portal.view'])
            ->pluck('id')
            ->all();

        $portalRole->permissions()->sync($portalPermissions);

        $primaryGuardian = Guardian::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'parent1@example.com')->firstOrFail();
        $primaryStudent = Student::withoutGlobalScopes()->where('school_id', $school->id)->where('admission_no', 'ADM-2026-0001')->firstOrFail();

        $secondStudent = Student::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'admission_no' => 'ADM-2026-0002',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Aarav',
                'middle_name' => null,
                'last_name' => 'Sharma',
                'full_name' => 'Aarav Sharma',
                'roll_no' => '10A-02',
                'preferred_name' => 'Aarav',
                'email' => 'aarav.sharma@student.greenwood.edu',
                'phone' => '7777777702',
                'gender' => 'male',
                'date_of_birth' => '2012-03-22',
                'admission_date' => '2026-04-10',
                'joining_date' => '2026-04-10',
                'status' => 'active',
                'current_status' => 'active',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $secondStudent->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-02',
                'enrollment_date' => '2026-04-10',
                'status' => 'enrolled',
                'is_current' => true,
                'joined_on' => '2026-04-10',
            ]
        );

        $primaryGuardian->students()->syncWithoutDetaching([
            $secondStudent->id => [
                'school_id' => $school->id,
                'relationship' => 'Mother',
                'relationship_label' => 'Mother',
                'is_primary' => false,
                'is_emergency_contact' => true,
                'pickup_authorized' => true,
            ],
        ]);

        $bothGuardian = Guardian::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'portal.guardian.both@example.com',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Nisha',
                'middle_name' => null,
                'last_name' => 'Rao',
                'full_name' => 'Nisha Rao',
                'phone' => '8888888899',
                'alternate_phone' => '8888888898',
                'relationship_type' => 'Sister',
                'occupation' => 'Research Scholar',
                'can_receive_sms' => true,
                'can_receive_email' => true,
                'can_pickup_student' => true,
                'status' => 'active',
            ]
        );

        $bothStudent = Student::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'admission_no' => 'ADM-2026-0003',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Nisha',
                'middle_name' => null,
                'last_name' => 'Rao',
                'full_name' => 'Nisha Rao',
                'roll_no' => '10A-03',
                'preferred_name' => 'Nisha',
                'email' => 'nisha.rao@student.greenwood.edu',
                'phone' => '7777777703',
                'gender' => 'female',
                'date_of_birth' => '2010-11-04',
                'admission_date' => '2026-04-10',
                'joining_date' => '2026-04-10',
                'status' => 'active',
                'current_status' => 'active',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $bothStudent->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-03',
                'enrollment_date' => '2026-04-10',
                'status' => 'enrolled',
                'is_current' => true,
                'joined_on' => '2026-04-10',
            ]
        );

        $guardianChild = Student::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'admission_no' => 'ADM-2026-0004',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Ira',
                'middle_name' => null,
                'last_name' => 'Rao',
                'full_name' => 'Ira Rao',
                'roll_no' => '10A-04',
                'preferred_name' => 'Ira',
                'email' => 'ira.rao@student.greenwood.edu',
                'phone' => '7777777704',
                'gender' => 'female',
                'date_of_birth' => '2013-09-18',
                'admission_date' => '2026-04-10',
                'joining_date' => '2026-04-10',
                'status' => 'active',
                'current_status' => 'active',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $guardianChild->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-04',
                'enrollment_date' => '2026-04-10',
                'status' => 'enrolled',
                'is_current' => true,
                'joined_on' => '2026-04-10',
            ]
        );

        $bothGuardian->students()->syncWithoutDetaching([
            $guardianChild->id => [
                'school_id' => $school->id,
                'relationship' => 'Guardian',
                'relationship_label' => 'Guardian',
                'is_primary' => true,
                'is_emergency_contact' => true,
                'pickup_authorized' => true,
            ],
        ]);

        $studentUser = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'portal.student@greenwood.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riya',
                'last_name' => 'Student',
                'name' => 'Riya Student',
                'phone' => '9666666601',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $studentUser->roles()->syncWithoutDetaching([$portalRole->id => ['school_id' => $school->id]]);

        $guardianUser = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'portal.guardian@greenwood.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Asha',
                'last_name' => 'Guardian',
                'name' => 'Asha Guardian',
                'phone' => '9666666602',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $guardianUser->roles()->syncWithoutDetaching([$portalRole->id => ['school_id' => $school->id]]);

        $bothUser = User::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'portal.both@greenwood.edu',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Nisha',
                'last_name' => 'Both',
                'name' => 'Nisha Both',
                'phone' => '9666666603',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );
        $bothUser->roles()->syncWithoutDetaching([$portalRole->id => ['school_id' => $school->id]]);

        $primaryStudent->update(['user_id' => $studentUser->id]);
        $bothStudent->update(['user_id' => $bothUser->id]);

        $this->seedProfilesAndAccess($school->id, $studentUser, $primaryStudent, null, 'self', true);
        $this->seedProfilesAndAccess($school->id, $guardianUser, $primaryStudent, $primaryGuardian, 'parent', true);
        $this->seedProfilesAndAccess($school->id, $guardianUser, $secondStudent, $primaryGuardian, 'parent', false);
        $this->seedProfilesAndAccess($school->id, $bothUser, $bothStudent, null, 'self', true);
        $this->seedProfilesAndAccess($school->id, $bothUser, $guardianChild, $bothGuardian, 'guardian', false);

        PortalUserProfile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $studentUser->id,
                'profile_type' => 'student',
                'profile_id' => $primaryStudent->id,
            ],
            [
                'is_default' => true,
                'status' => 'active',
            ]
        );

        PortalUserProfile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $guardianUser->id,
                'profile_type' => 'guardian',
                'profile_id' => $primaryGuardian->id,
            ],
            [
                'is_default' => true,
                'status' => 'active',
            ]
        );

        PortalUserProfile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $bothUser->id,
                'profile_type' => 'student',
                'profile_id' => $bothStudent->id,
            ],
            [
                'is_default' => true,
                'status' => 'active',
            ]
        );

        PortalUserProfile::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $bothUser->id,
                'profile_type' => 'guardian',
                'profile_id' => $bothGuardian->id,
            ],
            [
                'is_default' => false,
                'status' => 'active',
            ]
        );

        PortalSession::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $studentUser->id,
            ],
            [
                'active_profile_type' => 'student',
                'active_profile_id' => $primaryStudent->id,
                'active_student_id' => $primaryStudent->id,
                'last_seen_at' => now(),
                'device_info' => ['seeded' => true],
                'ip_address' => '127.0.0.1',
            ]
        );

        PortalSession::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $guardianUser->id,
            ],
            [
                'active_profile_type' => 'guardian',
                'active_profile_id' => $primaryGuardian->id,
                'active_student_id' => $primaryStudent->id,
                'last_seen_at' => now(),
                'device_info' => ['seeded' => true],
                'ip_address' => '127.0.0.1',
            ]
        );

        PortalSession::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $bothUser->id,
            ],
            [
                'active_profile_type' => 'student',
                'active_profile_id' => $bothStudent->id,
                'active_student_id' => $bothStudent->id,
                'last_seen_at' => now(),
                'device_info' => ['seeded' => true],
                'ip_address' => '127.0.0.1',
            ]
        );

        PortalNotification::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $studentUser->id,
                'student_id' => $primaryStudent->id,
                'title' => 'Attendance Alert',
            ],
            [
                'message' => 'Your attendance summary is available in the portal.',
                'notification_type' => 'attendance',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        PortalNotification::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_id' => $guardianUser->id,
                'student_id' => $primaryStudent->id,
                'title' => 'Fee Reminder',
            ],
            [
                'message' => 'Pending fee dues are available for review.',
                'notification_type' => 'fee',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        AttendanceSummary::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'user_type' => 'student',
                'user_id' => $primaryStudent->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'total_days' => 100,
                'present_days' => 92,
                'absent_days' => 5,
                'leave_days' => 2,
                'late_days' => 1,
                'percentage' => 92.00,
                'last_updated_at' => now(),
            ]
        );

        FeeInvoice::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'invoice_no' => 'PORTAL-FEE-0001',
            ],
            [
                'student_id' => $primaryStudent->id,
                'academic_year_id' => $academicYear->id,
                'issue_date' => now()->subDays(10)->toDateString(),
                'due_date' => now()->addDays(15)->toDateString(),
                'subtotal' => 25000,
                'discount_total' => 0,
                'fine_total' => 0,
                'tax_total' => 0,
                'grand_total' => 25000,
                'paid_amount' => 15000,
                'balance_amount' => 10000,
                'status' => 'partial',
                'notes' => 'Seeded portal fee invoice.',
                'created_by' => $admin->id,
            ]
        );

        $latestResult = StudentResult::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('student_id', $primaryStudent->id)
            ->latest('computed_at')
            ->first();

        if (! $latestResult) {
            StudentResult::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'exam_id' => 1,
                    'student_id' => $primaryStudent->id,
                ],
                [
                    'total_marks' => 500,
                    'obtained_marks' => 430,
                    'percentage' => 86.00,
                    'grade' => 'A',
                    'gpa' => 4.00,
                    'result_status' => 'pass',
                    'rank' => 1,
                    'remarks' => 'Seeded portal result.',
                    'computed_at' => now(),
                ]
            );
        }
    }

    protected function seedProfilesAndAccess(
        int $schoolId,
        User $user,
        Student $student,
        ?Guardian $guardian,
        string $accessType,
        bool $canPayFees
    ): void {
        PortalProfileAccess::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $schoolId,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'access_type' => $accessType,
            ],
            [
                'guardian_id' => $guardian?->id,
                'can_view_attendance' => true,
                'can_view_fees' => true,
                'can_pay_fees' => $canPayFees,
                'can_view_results' => true,
                'can_view_documents' => true,
                'can_message_teacher' => true,
                'status' => 'active',
            ]
        );
    }
}
