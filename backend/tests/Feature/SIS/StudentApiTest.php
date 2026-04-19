<?php

namespace Tests\Feature\SIS;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Section;
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
            'blood_group' => 'O+',
            'status' => 'active',
            'address' => [
                'line1' => '45 Residency Road',
                'city' => 'Bengaluru',
            ],
            'medical_notes' => 'N/A',
            'guardians' => [
                [
                    'id' => $guardian->id,
                    'relationship' => 'Mother',
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
            'metadata' => [
                'issued_by' => 'Municipal Office',
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.student_id', $student->id)
            ->assertJsonPath('data.document_type', 'birth_certificate');

        $this->assertDatabaseHas('student_documents', [
            'school_id' => $user->school_id,
            'student_id' => $student->id,
            'document_type' => 'birth_certificate',
        ]);
    }
}
