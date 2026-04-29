<?php

namespace Tests\Feature\Examination;

use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\Examination\Exam;
use App\Models\Examination\ExamType;
use App\Models\Examination\GradingSystem;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentEnrollment;
use App\Models\StudentHouse;
use App\Models\User;
use App\Services\Examination\GradingService;
use App\Support\Auth\JwtManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExaminationApiTest extends TestCase
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

    public function test_authorized_user_can_create_exam(): void
    {
        $headers = $this->authenticate();
        $examType = ExamType::withoutGlobalScopes()->where('code', 'UNIT')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('is_current', true)->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('code', 'G10')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_class_id', $class->id)->where('name', 'A')->firstOrFail();
        $term = AcademicTerm::withoutGlobalScopes()->where('academic_year_id', $academicYear->id)->orderBy('sequence')->firstOrFail();

        $response = $this->withHeaders($headers)->postJson('/api/v1/exams', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Practice Unit Test',
            'code' => 'UNIT-PRACTICE-01',
            'exam_type_id' => $examType->id,
            'term_id' => $term->id,
            'class_id' => $class->id,
            'section_id' => $section->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'total_marks' => 100,
            'passing_marks' => 35,
            'result_status' => 'draft',
        ])->assertCreated();

        $this->assertDatabaseHas('exams', [
            'id' => $response->json('data.id'),
            'code' => 'UNIT-PRACTICE-01',
        ]);
    }

    public function test_subject_can_be_assigned_to_exam(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $science = $this->createSubjectForExamClass($exam, 'SCIENCE', 'Science');

        $this->withHeaders($headers)->postJson('/api/v1/exams/subjects', [
            'exam_id' => $exam->id,
            'subject_id' => $science->id,
            'max_marks' => 100,
            'passing_marks' => 35,
            'weightage' => 100,
        ])->assertCreated()->assertJsonPath('data.subject_id', $science->id);

        $this->assertDatabaseHas('exam_subjects', [
            'exam_id' => $exam->id,
            'subject_id' => $science->id,
        ]);
    }

    public function test_marks_can_be_entered_singly_and_in_bulk(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $science = $this->createSubjectForExamClass($exam, 'BIOLOGY', 'Biology');
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();
        $secondStudent = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0002')->firstOrFail();

        $this->withHeaders($headers)->postJson('/api/v1/exams/subjects', [
            'exam_id' => $exam->id,
            'subject_id' => $science->id,
            'max_marks' => 50,
            'passing_marks' => 18,
            'weightage' => 50,
        ])->assertCreated();

        $this->withHeaders($headers)->postJson('/api/v1/exams/marks', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $science->id,
            'marks_obtained' => 44,
            'remarks' => 'Strong paper.',
        ])->assertCreated()->assertJsonPath('data.student_id', $student->id);

        $this->withHeaders($headers)->postJson('/api/v1/exams/marks/bulk', [
            'exam_id' => $exam->id,
            'records' => [
                [
                    'student_id' => $secondStudent->id,
                    'subject_id' => $science->id,
                    'marks_obtained' => 39,
                    'remarks' => 'Good effort.',
                ],
            ],
        ])->assertOk()->assertJsonCount(1, 'data');

        $this->assertDatabaseHas('exam_marks', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $science->id,
            'marks_obtained' => 44.00,
        ]);

        $this->assertDatabaseHas('exam_marks', [
            'exam_id' => $exam->id,
            'student_id' => $secondStudent->id,
            'subject_id' => $science->id,
            'marks_obtained' => 39.00,
        ]);
    }

    public function test_results_can_be_computed(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $gradingSystem = GradingSystem::withoutGlobalScopes()->where('code', 'STD-PERCENT')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/exams/results/{$exam->id}/compute", [
            'grading_system_id' => $gradingSystem->id,
        ])->assertOk()->assertJsonPath('data.0.exam_id', $exam->id);

        $this->assertDatabaseHas('student_results', [
            'exam_id' => $exam->id,
            'student_id' => Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->value('id'),
            'result_status' => 'pass',
        ]);
    }

    public function test_grading_mapping_returns_expected_grade(): void
    {
        $this->seed();

        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $system = GradingSystem::withoutGlobalScopes()->where('code', 'STD-PERCENT')->firstOrFail();
        $grading = app(GradingService::class)->mapPercentage(88.5, gradingSystem: $system, schoolId: $school->id);

        $this->assertSame('A', $grading['grade']);
        $this->assertSame(3.7, $grading['gpa']);
        $this->assertTrue($grading['is_pass']);
    }

    public function test_merit_list_assigns_expected_ranks(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $gradingSystem = GradingSystem::withoutGlobalScopes()->where('code', 'STD-PERCENT')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/exams/results/{$exam->id}/compute", [
            'grading_system_id' => $gradingSystem->id,
        ])->assertOk();

        $response = $this->withHeaders($headers)->getJson("/api/v1/exams/results/{$exam->id}/merit-list")
            ->assertOk();

        $this->assertSame(1, $response->json('data.0.rank'));
        $this->assertSame(2, $response->json('data.1.rank'));
        $this->assertSame(3, $response->json('data.2.rank'));
    }

    public function test_results_can_be_published_and_notifications_created(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $gradingSystem = GradingSystem::withoutGlobalScopes()->where('code', 'STD-PERCENT')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/exams/results/{$exam->id}/compute", [
            'grading_system_id' => $gradingSystem->id,
        ])->assertOk();

        $this->withHeaders($headers)->postJson("/api/v1/exams/results/{$exam->id}/publish", [
            'is_public' => true,
            'notify_users' => true,
        ])->assertOk()->assertJsonPath('data.exam_id', $exam->id);

        $this->assertDatabaseHas('result_publications', [
            'exam_id' => $exam->id,
            'is_public' => 1,
        ]);

        $this->assertDatabaseHas('notification_logs', [
            'notifiable_type' => 'student',
            'notifiable_id' => $student->id,
            'channel' => 'in_app',
            'subject' => 'Unit Test 1 results published',
        ]);
    }

    public function test_revaluation_request_can_be_created(): void
    {
        $headers = $this->authenticate();
        $exam = Exam::withoutGlobalScopes()->where('code', 'UNIT-TEST-1')->firstOrFail();
        $gradingSystem = GradingSystem::withoutGlobalScopes()->where('code', 'STD-PERCENT')->firstOrFail();
        $student = Student::withoutGlobalScopes()->where('admission_no', 'ADM-2026-0001')->firstOrFail();
        $subject = Subject::withoutGlobalScopes()->where('code', 'MATH')->firstOrFail();

        $this->withHeaders($headers)->postJson("/api/v1/exams/results/{$exam->id}/compute", [
            'grading_system_id' => $gradingSystem->id,
        ])->assertOk();

        $this->withHeaders($headers)->postJson('/api/v1/exams/revaluation', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'reason' => 'Please review step-marking for algebra solutions.',
            'requested_at' => now()->toDateTimeString(),
        ])->assertCreated()->assertJsonPath('data.student_id', $student->id);

        $this->assertDatabaseHas('revaluation_requests', [
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'status' => 'pending',
        ]);
    }

    protected function createSubjectForExamClass(Exam $exam, string $code, string $name): Subject
    {
        $subject = Subject::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $exam->school_id,
                'code' => $code,
            ],
            [
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'type' => 'mandatory',
                'description' => $name.' subject for examination tests.',
                'status' => 'active',
            ]
        );

        ClassSubjectAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $exam->school_id,
                'academic_year_id' => $exam->academic_year_id,
                'school_class_id' => $exam->class_id,
                'section_id' => $exam->section_id,
                'subject_id' => $subject->id,
            ],
            [
                'is_optional' => false,
                'weekly_periods' => 4,
                'status' => 'active',
            ]
        );

        return $subject;
    }
}
