<?php

namespace Tests\Feature\AcademicManagement;

use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): array
    {
        $this->seed();

        $user = User::withoutGlobalScopes()->where('email', 'admin@greenwood.edu')->firstOrFail();
        $token = app(JwtManager::class)->issueAccessToken($user);

        return [
            'Authorization' => 'Bearer '.$token,
            'X-Tenant-Code' => 'greenwood',
        ];
    }

    public function test_only_one_academic_year_can_be_active(): void
    {
        $headers = $this->authenticate();

        $response = $this->withHeaders($headers)->postJson('/api/v1/academic-management/academic-years', [
            'name' => 'Academic Year 2027-2028',
            'code' => 'AY-2027-28',
            'start_date' => '2027-04-01',
            'end_date' => '2028-03-31',
            'is_active' => true,
            'status' => 'active',
        ]);

        $response->assertCreated()->assertJsonPath('data.code', 'AY-2027-28');

        $this->assertDatabaseCount('academic_years', 2);
        $this->assertDatabaseHas('academic_years', ['code' => 'AY-2027-28', 'is_active' => true]);
        $this->assertDatabaseHas('academic_years', ['code' => 'AY-2026-27', 'is_active' => false]);
    }

    public function test_term_dates_must_fall_within_academic_year(): void
    {
        $headers = $this->authenticate();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('code', 'AY-2026-27')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/academic-management/terms', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Invalid Term',
            'code' => 'INV',
            'start_date' => '2026-03-01',
            'end_date' => '2026-04-15',
            'sequence' => 3,
            'status' => 'active',
        ])->assertStatus(422);
    }

    public function test_class_subject_assignment_must_be_unique(): void
    {
        $headers = $this->authenticate();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('code', 'AY-2026-27')->firstOrFail();
        $schoolClass = SchoolClass::withoutGlobalScopes()->where('code', 'G8')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('code', 'A')->firstOrFail();
        $subject = Subject::withoutGlobalScopes()->where('code', 'MATH')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/academic-management/class-subjects', [
            'academic_year_id' => $academicYear->id,
            'school_class_id' => $schoolClass->id,
            'section_id' => $section->id,
            'subject_id' => $subject->id,
            'is_optional' => false,
            'weekly_periods' => 5,
            'status' => 'active',
        ])->assertStatus(422);
    }

    public function test_options_endpoint_returns_dropdown_data(): void
    {
        $headers = $this->authenticate();

        $this->withHeaders($headers)->getJson('/api/v1/academic-management/options')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'academic_years',
                    'terms',
                    'classes',
                    'sections',
                    'subjects',
                    'grading_structures',
                    'staff',
                ],
            ]);
    }
}
