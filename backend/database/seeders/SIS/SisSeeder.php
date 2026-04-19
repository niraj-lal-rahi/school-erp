<?php

namespace Database\Seeders\SIS;

use App\Models\AcademicYear;
use App\Models\Admission;
use App\Models\Guardian;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentDocument;
use App\Models\StudentHouse;
use App\Models\StudentEnrollment;
use App\Models\StudentMedicalRecord;
use App\Models\StudentNote;
use App\Models\StudentStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SisSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $school->id)->where('is_current', true)->firstOrFail();
        $user = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();

        $class = SchoolClass::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'code' => 'G10',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Grade 10',
                'grade_level' => 10,
                'sort_order' => 10,
            ]
        );

        $section = Section::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'school_class_id' => $class->id,
                'name' => 'A',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'capacity' => 35,
                'class_teacher_id' => $user->id,
            ]
        );

        $guardian = Guardian::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'email' => 'parent1@example.com',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Asha',
                'middle_name' => null,
                'last_name' => 'Sharma',
                'full_name' => 'Asha Sharma',
                'phone' => '8888888888',
                'alternate_phone' => '8888888800',
                'relationship_type' => 'Mother',
                'occupation' => 'Architect',
                'can_receive_sms' => true,
                'can_receive_email' => true,
                'can_pickup_student' => true,
                'status' => 'active',
                'address' => [
                    'line1' => '12 MG Road',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'postal_code' => '560001',
                ],
                'address_line1' => '12 MG Road',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
            ]
        );

        $category = StudentCategory::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'GEN',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'General',
                'status' => 'active',
            ]
        );

        $house = StudentHouse::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'BLUE',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Blue House',
                'color' => '#1d4ed8',
                'status' => 'active',
            ]
        );

        $student = Student::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'admission_no' => 'ADM-2026-0001',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'first_name' => 'Riya',
                'middle_name' => null,
                'last_name' => 'Sharma',
                'full_name' => 'Riya Sharma',
                'roll_no' => '10A-01',
                'preferred_name' => 'Riya',
                'email' => 'riya.sharma@student.greenwood.edu',
                'phone' => '7777777777',
                'gender' => 'female',
                'date_of_birth' => '2011-08-14',
                'admission_date' => '2026-04-10',
                'joining_date' => '2026-04-10',
                'blood_group' => 'B+',
                'status' => 'active',
                'current_status' => 'active',
                'category_id' => $category->id,
                'house_id' => $house->id,
                'address' => [
                    'line1' => '12 MG Road',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'postal_code' => '560001',
                ],
                'medical_notes' => 'Peanut allergy',
                'notes' => 'Needs front-row seating during math sessions.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        $student->guardians()->syncWithoutDetaching([
            $guardian->id => [
                'school_id' => $school->id,
                'relationship' => 'Mother',
                'relationship_label' => 'Mother',
                'is_primary' => true,
                'is_emergency_contact' => true,
                'pickup_authorized' => true,
            ],
        ]);

        Admission::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'application_no' => 'APP-2026-0001',
                'academic_year_id' => $academicYear->id,
                'applied_class_id' => $class->id,
                'section_id' => $section->id,
                'first_name' => 'Riya',
                'last_name' => 'Sharma',
                'gender' => 'female',
                'date_of_birth' => '2011-08-14',
                'guardian_name' => 'Asha Sharma',
                'guardian_phone' => '8888888888',
                'guardian_email' => 'parent1@example.com',
                'address_line1' => '12 MG Road',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'country' => 'India',
                'postal_code' => '560001',
                'status' => 'admitted',
                'application_status' => 'converted',
                'applied_on' => '2026-04-01',
                'admitted_on' => '2026-04-10',
                'remarks' => 'Scholarship review completed.',
                'reviewed_by' => $user->id,
                'reviewed_at' => '2026-04-05 10:00:00',
            ]
        );

        StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-01',
                'enrollment_date' => '2026-04-10',
                'status' => 'enrolled',
                'is_current' => true,
                'joined_on' => '2026-04-10',
            ]
        );

        StudentDocument::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'title' => 'Birth Certificate',
            ],
            [
                'uploaded_by' => $user->id,
                'document_type' => 'birth_certificate',
                'disk' => 'local',
                'file_path' => 'students/'.$student->id.'/documents/birth-certificate.pdf',
                'file_name' => 'birth-certificate.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 102400,
                'issued_by' => 'Municipal Office',
                'issued_date' => '2011-08-16',
                'verification_status' => 'verified',
                'remarks' => 'Verified at admission time.',
                'metadata' => [
                    'original_name' => 'birth-certificate.pdf',
                ],
            ]
        );

        StudentMedicalRecord::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
            ],
            [
                'blood_group' => 'B+',
                'height' => 148.50,
                'weight' => 39.20,
                'allergies' => 'Peanut allergy',
                'medical_conditions' => 'Mild seasonal asthma',
                'medications' => 'Rescue inhaler as needed',
                'doctor_name' => 'Dr. Meera Nair',
                'doctor_phone' => '9000000001',
                'hospital_name' => 'City Children Hospital',
                'emergency_contact_name' => 'Asha Sharma',
                'emergency_contact_phone' => '8888888888',
                'insurance_provider' => 'Star Health',
                'insurance_number' => 'POL-2026-1001',
                'notes' => 'Keep inhaler available during sports.',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        StudentStatusHistory::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'action_type' => 'admit',
            ],
            [
                'previous_status' => 'applicant',
                'new_status' => 'active',
                'reason' => 'Admission approved and student onboarded.',
                'effective_date' => '2026-04-10',
                'performed_by' => $user->id,
            ]
        );

        StudentNote::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'student_id' => $student->id,
                'visibility_type' => 'internal',
            ],
            [
                'note' => 'Student responds well to project-based learning.',
                'created_by' => $user->id,
            ]
        );
    }
}
