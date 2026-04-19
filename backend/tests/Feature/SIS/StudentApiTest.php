<?php

namespace Tests\Feature\SIS;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_student(): void
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $user->school_id)->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $guardian = Guardian::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->postJson('/api/v1/students', [
            'admission_no' => 'ADM-2026-0002',
            'first_name' => 'Kabir',
            'last_name' => 'Verma',
            'preferred_name' => 'Kabir',
            'email' => 'kabir.verma@student.greenwood.edu',
            'phone' => '7666666666',
            'gender' => 'male',
            'date_of_birth' => '2012-05-11',
            'admission_date' => '2026-04-11',
            'joining_date' => '2026-04-11',
            'blood_group' => 'O+',
            'current_status' => 'active',
            'address' => [
                'line1' => '45 Residency Road',
                'city' => 'Bengaluru',
            ],
            'medical_notes' => 'N/A',
            'guardians' => [
                [
                    'id' => $guardian->id,
                    'relationship' => 'Mother',
                    'relationship_label' => 'Mother',
                    'is_primary' => true,
                    'is_emergency_contact' => true,
                    'pickup_authorized' => true,
                ],
            ],
            'enrollment' => [
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'roll_number' => '10A-02',
                'status' => 'active',
                'joined_on' => '2026-04-11',
            ],
            'admission' => [
                'academic_year_id' => $academicYear->id,
                'applied_class_id' => $class->id,
                'status' => 'admitted',
                'applied_on' => '2026-04-05',
                'admitted_on' => '2026-04-11',
                'remarks' => 'Transferred from another school',
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.admission_no', 'ADM-2026-0002')
            ->assertJsonPath('data.first_name', 'Kabir');

        $this->assertDatabaseHas('students', [
            'school_id' => $user->school_id,
            'admission_no' => 'ADM-2026-0002',
        ]);
    }

    public function test_authorized_user_can_upload_student_document(): void
    {
        Storage::fake('local');
        config()->set('filesystems.default', 'local');

        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = \App\Models\Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->postJson('/api/v1/students/'.$student->id.'/documents', [
            'document_type' => 'birth_certificate',
            'title' => 'Birth Certificate',
            'file' => UploadedFile::fake()->create('birth-certificate.pdf', 120, 'application/pdf'),
            'issued_by' => 'Municipal Office',
            'issued_date' => '2011-08-16',
            'verification_status' => 'verified',
            'remarks' => 'Uploaded during admission review.',
            'metadata' => [
                'source' => 'frontdesk',
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.document_type', 'birth_certificate')
            ->assertJsonPath('data.issued_by', 'Municipal Office')
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertDatabaseHas('student_documents', [
            'school_id' => $user->school_id,
            'student_id' => $student->id,
            'document_type' => 'birth_certificate',
            'issued_by' => 'Municipal Office',
            'verification_status' => 'verified',
        ]);
    }

    public function test_authorized_user_can_assign_and_remove_guardian(): void
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $guardian = Guardian::withoutGlobalScopes()->create([
            'school_id' => $user->school_id,
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'first_name' => 'Ramesh',
            'last_name' => 'Sharma',
            'full_name' => 'Ramesh Sharma',
            'phone' => '9999999999',
            'status' => 'active',
        ]);
        $token = app(JwtManager::class)->issueAccessToken($user);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->postJson('/api/v1/students/'.$student->id.'/assign-guardian', [
            'guardian_id' => $guardian->id,
            'relationship' => 'Father',
            'relationship_label' => 'Father',
            'pickup_authorized' => true,
        ])->assertOk()->assertJsonFragment([
            'id' => $guardian->id,
            'full_name' => 'Ramesh Sharma',
        ]);

        $this->assertDatabaseHas('student_guardian', [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
            'relationship_label' => 'Father',
        ]);

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->deleteJson('/api/v1/students/'.$student->id.'/remove-guardian/'.$guardian->id)
            ->assertOk();

        $this->assertDatabaseMissing('student_guardian', [
            'student_id' => $student->id,
            'guardian_id' => $guardian->id,
        ]);
    }

    public function test_authorized_user_can_save_student_medical_record(): void
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ])->putJson('/api/v1/students/'.$student->id.'/medical', [
            'blood_group' => 'AB+',
            'height' => 150.4,
            'weight' => 40.8,
            'allergies' => 'Dust',
            'medical_conditions' => 'Seasonal asthma',
            'doctor_name' => 'Dr. Meera Nair',
            'doctor_phone' => '9000000001',
            'hospital_name' => 'City Children Hospital',
            'emergency_contact_name' => 'Asha Sharma',
            'emergency_contact_phone' => '8888888888',
            'notes' => 'Carry inhaler during sports practice.',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.blood_group', 'AB+')
            ->assertJsonPath('data.allergies', 'Dust');

        $this->assertDatabaseHas('student_medical_records', [
            'school_id' => $user->school_id,
            'student_id' => $student->id,
            'blood_group' => 'AB+',
        ]);
    }
}
