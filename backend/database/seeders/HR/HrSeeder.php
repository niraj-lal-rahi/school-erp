<?php

namespace Database\Seeders\HR;

use App\Models\AcademicYear;
use App\Models\HR\Department;
use App\Models\HR\Designation;
use App\Models\HR\LeaveBalance;
use App\Models\HR\LeaveType;
use App\Models\HR\PayrollRun;
use App\Models\HR\SalaryComponent;
use App\Models\HR\SalaryStructure;
use App\Models\HR\Staff;
use App\Models\HR\StaffAttendance;
use App\Models\HR\StaffBankDetail;
use App\Models\HR\StaffEmergencyContact;
use App\Models\HR\StaffLeaveApplication;
use App\Models\HR\StaffNote;
use App\Models\HR\StaffPayslip;
use App\Models\HR\StaffQualification;
use App\Models\HR\StaffStatusHistory;
use App\Models\HR\StaffWorkExperience;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $administration = Department::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'ADMIN'],
            [
                'name' => 'Administration',
                'description' => 'School administration and leadership team.',
                'status' => 'active',
            ]
        );

        $teaching = Department::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'TEACH'],
            [
                'name' => 'Teaching',
                'description' => 'Faculty and classroom teaching staff.',
                'status' => 'active',
            ]
        );

        $principal = Designation::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'PRINCIPAL'],
            [
                'department_id' => $administration->id,
                'name' => 'Principal',
                'description' => 'School principal',
                'status' => 'active',
            ]
        );

        $teacher = Designation::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'TEACHER'],
            [
                'department_id' => $teaching->id,
                'name' => 'Teacher',
                'description' => 'Classroom teacher',
                'status' => 'active',
            ]
        );

        Staff::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'employee_code' => 'EMP-0001'],
            [
                'user_id' => $admin->id,
                'department_id' => $administration->id,
                'designation_id' => $principal->id,
                'first_name' => 'School',
                'middle_name' => null,
                'last_name' => 'Admin',
                'full_name' => 'School Admin',
                'gender' => 'other',
                'date_of_birth' => null,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'alternate_phone' => null,
                'photo_path' => null,
                'staff_type' => 'admin',
                'employment_type' => 'full_time',
                'joining_date' => now()->subYears(2)->toDateString(),
                'leaving_date' => null,
                'current_status' => 'active',
                'qualification_summary' => 'School leadership and administration',
                'experience_years' => 10,
                'address_line1' => null,
                'address_line2' => null,
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
                'notes' => 'Default seeded school administrator.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        Staff::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'employee_code' => 'EMP-0002'],
            [
                'user_id' => null,
                'department_id' => $teaching->id,
                'designation_id' => $teacher->id,
                'first_name' => 'Meera',
                'middle_name' => null,
                'last_name' => 'Nair',
                'full_name' => 'Meera Nair',
                'gender' => 'female',
                'date_of_birth' => '1989-07-12',
                'email' => 'meera.nair@greenwood.edu',
                'phone' => '9000000011',
                'alternate_phone' => '9000000012',
                'photo_path' => null,
                'staff_type' => 'teaching',
                'employment_type' => 'full_time',
                'joining_date' => now()->subYear()->toDateString(),
                'leaving_date' => null,
                'current_status' => 'active',
                'qualification_summary' => 'M.Sc. Mathematics, B.Ed.',
                'experience_years' => 7.5,
                'address_line1' => '12 School Road',
                'address_line2' => null,
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
                'notes' => 'Seeded sample teaching staff.',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $teacherStaff = Staff::withoutGlobalScopes()->where('school_id', $school->id)->where('employee_code', 'EMP-0002')->firstOrFail();
        $academicYearId = AcademicYear::withoutGlobalScopes()
            ->where('school_id', $school->id)
            ->where('is_current', true)
            ->value('id');
        $casualLeave = LeaveType::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'CL'],
            [
                'name' => 'Casual Leave',
                'annual_quota' => 12,
                'carry_forward_allowed' => false,
                'paid_leave' => true,
                'status' => 'active',
            ]
        );

        StaffEmergencyContact::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'staff_id' => $teacherStaff->id, 'phone' => '9444444444'],
            [
                'contact_name' => 'Anita Nair',
                'relationship' => 'Spouse',
                'alternate_phone' => null,
                'email' => 'anita.nair@example.com',
                'address' => '12 School Road, Bengaluru',
                'is_primary' => true,
            ]
        );

        StaffQualification::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'staff_id' => $teacherStaff->id, 'degree' => 'M.Sc. Mathematics'],
            [
                'institution' => 'Bangalore University',
                'board_or_university' => 'Bangalore University',
                'specialization' => 'Applied Mathematics',
                'passing_year' => 2011,
                'percentage_or_grade' => '78%',
                'document_path' => null,
            ]
        );

        StaffWorkExperience::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'staff_id' => $teacherStaff->id, 'organization_name' => 'Springfield High School'],
            [
                'designation' => 'Mathematics Teacher',
                'start_date' => '2018-06-01',
                'end_date' => '2025-03-31',
                'is_current' => false,
                'responsibilities' => 'Taught middle school mathematics and coordinated olympiad prep.',
                'experience_letter_path' => null,
            ]
        );

        StaffAttendance::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'staff_id' => $teacherStaff->id, 'attendance_date' => now()->toDateString()],
            [
                'check_in_time' => '08:25',
                'check_out_time' => '15:45',
                'attendance_status' => 'present',
                'source' => 'manual',
                'remarks' => 'Seeded sample attendance.',
                'marked_by' => $admin->id,
            ]
        );

        LeaveBalance::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $teacherStaff->id,
                'leave_type_id' => $casualLeave->id,
                'academic_year_id' => $academicYearId,
            ],
            [
                'allocated_days' => 12,
                'used_days' => 0,
                'remaining_days' => 12,
                'carried_forward_days' => 0,
            ]
        );

        StaffLeaveApplication::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $teacherStaff->id,
                'leave_type_id' => $casualLeave->id,
                'start_date' => now()->addDays(2)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'total_days' => 2,
                'reason' => 'Family travel',
                'attachment_path' => null,
                'status' => 'submitted',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_remarks' => null,
            ]
        );

        $hra = SalaryComponent::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'HRA'],
            [
                'name' => 'House Rent Allowance',
                'component_type' => 'earning',
                'calculation_type' => 'fixed',
                'default_value' => 15000,
                'taxable' => true,
                'status' => 'active',
            ]
        );

        $pf = SalaryComponent::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'PF'],
            [
                'name' => 'Provident Fund',
                'component_type' => 'deduction',
                'calculation_type' => 'fixed',
                'default_value' => 3000,
                'taxable' => false,
                'status' => 'active',
            ]
        );

        $salaryStructure = SalaryStructure::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $teacherStaff->id,
                'effective_from' => now()->startOfMonth()->toDateString(),
            ],
            [
                'effective_to' => null,
                'basic_salary' => 50000,
                'gross_salary' => 65000,
                'net_salary' => 62000,
                'status' => 'active',
            ]
        );

        $salaryStructure->items()->delete();
        $salaryStructure->items()->createMany([
            [
                'school_id' => $school->id,
                'salary_component_id' => $hra->id,
                'amount' => 15000,
                'percentage' => null,
            ],
            [
                'school_id' => $school->id,
                'salary_component_id' => $pf->id,
                'amount' => 3000,
                'percentage' => null,
            ],
        ]);

        PayrollRun::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'payroll_month' => (int) now()->format('m'),
                'payroll_year' => (int) now()->format('Y'),
            ],
            [
                'status' => 'draft',
                'total_gross' => null,
                'total_deductions' => null,
                'total_net' => null,
                'processed_by' => null,
                'processed_at' => null,
            ]
        );

        StaffBankDetail::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'staff_id' => $teacherStaff->id, 'account_number' => '123456789012'],
            [
                'bank_name' => 'State Bank of India',
                'account_holder_name' => 'Meera Nair',
                'ifsc_code' => 'SBIN0001234',
                'branch_name' => 'Bengaluru Main',
                'account_type' => 'Savings',
                'is_primary' => true,
            ]
        );

        StaffStatusHistory::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $teacherStaff->id,
                'action_type' => 'staff_seeded',
                'new_status' => 'active',
            ],
            [
                'previous_status' => null,
                'reason' => 'Initial HR seed record',
                'effective_date' => now()->subYear()->toDateString(),
                'performed_by' => $admin->id,
            ]
        );

        StaffNote::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'staff_id' => $teacherStaff->id,
                'note' => 'Strong performance in math olympiad mentoring.',
            ],
            [
                'visibility_type' => 'internal',
                'created_by' => $admin->id,
            ]
        );
    }
}
