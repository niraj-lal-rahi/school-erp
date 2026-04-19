<?php

namespace Tests\Feature\SIS;

use App\Models\AcademicYear;
use App\Models\Admission;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionApiTest extends TestCase
{
    use RefreshDatabase;

    protected function headers(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_user_can_create_and_submit_admission_application(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $user->school_id)->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $response = $this->withHeaders($headers)->postJson('/api/v1/student-admissions', [
            'application_no' => 'APP-2026-0002',
            'academic_year_id' => $academicYear->id,
            'applied_class_id' => $class->id,
            'first_name' => 'Anaya',
            'last_name' => 'Kapoor',
            'gender' => 'female',
            'date_of_birth' => '2013-01-10',
            'guardian_name' => 'Meera Kapoor',
            'guardian_phone' => '9000000001',
            'guardian_email' => 'meera.kapoor@example.com',
            'application_status' => 'draft',
        ]);

        $response->assertCreated()->assertJsonPath('data.application_no', 'APP-2026-0002');

        /** @var Admission $admission */
        $admission = Admission::withoutGlobalScopes()->where('application_no', 'APP-2026-0002')->firstOrFail();

        $this->withHeaders($headers)
            ->postJson('/api/v1/student-admissions/'.$admission->id.'/submit')
            ->assertOk()
            ->assertJsonPath('data.application_status', 'submitted');
    }

    public function test_approved_admission_can_be_converted_to_student(): void
    {
        $headers = $this->headers();
        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $user->school_id)->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_id', $user->school_id)->firstOrFail();

        $create = $this->withHeaders($headers)->postJson('/api/v1/student-admissions', [
            'application_no' => 'APP-2026-0003',
            'academic_year_id' => $academicYear->id,
            'applied_class_id' => $class->id,
            'section_id' => $section->id,
            'first_name' => 'Vihaan',
            'last_name' => 'Rao',
            'gender' => 'male',
            'date_of_birth' => '2012-09-08',
            'guardian_name' => 'Nikita Rao',
            'guardian_phone' => '9000000002',
            'guardian_email' => 'nikita.rao@example.com',
            'application_status' => 'submitted',
        ])->assertCreated();

        $admissionId = $create->json('data.id');

        $this->withHeaders($headers)
            ->postJson('/api/v1/student-admissions/'.$admissionId.'/approve', [
                'remarks' => 'Eligible for admission',
            ])
            ->assertOk()
            ->assertJsonPath('data.application_status', 'approved');

        $this->withHeaders($headers)
            ->postJson('/api/v1/student-admissions/'.$admissionId.'/convert-to-student', [
                'admission_no' => 'ADM-2026-0100',
                'roll_no' => '10A-10',
                'joining_date' => '2026-04-12',
                'section_id' => $section->id,
                'current_status' => 'active',
            ])
            ->assertOk()
            ->assertJsonPath('data.application_status', 'converted')
            ->assertJsonPath('data.student.admission_no', 'ADM-2026-0100');

        $this->assertDatabaseHas('students', [
            'school_id' => $user->school_id,
            'admission_no' => 'ADM-2026-0100',
            'first_name' => 'Vihaan',
        ]);
    }
}
