<?php

namespace Database\Seeders\Examination;

use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\Subject;
use App\Models\AcademicYear;
use App\Models\Examination\Exam;
use App\Models\Examination\ExamMark;
use App\Models\Examination\ExamSubject;
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
use App\Services\Examination\ResultComputationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoExamSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();
        $academicYear = AcademicYear::withoutGlobalScopes()->where('school_id', $school->id)->where('is_current', true)->firstOrFail();
        $term = AcademicTerm::withoutGlobalScopes()->where('school_id', $school->id)->orderBy('sequence')->firstOrFail();
        $class = SchoolClass::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'G10')->firstOrFail();
        $section = Section::withoutGlobalScopes()->where('school_id', $school->id)->where('school_class_id', $class->id)->where('name', 'A')->firstOrFail();
        $subject = Subject::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'MATH')->firstOrFail();
        $examType = ExamType::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'UNIT')->firstOrFail();
        $gradingSystem = GradingSystem::withoutGlobalScopes()->where('school_id', $school->id)->where('code', 'STD-PERCENT')->firstOrFail();

        ClassSubjectAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $class->id,
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ],
            [
                'is_optional' => false,
                'weekly_periods' => 5,
                'status' => 'active',
            ]
        );

        $students = $this->ensureDemoStudents($school->id, $class->id, $section->id, $academicYear->id, $admin->id);

        $exam = Exam::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'code' => 'UNIT-TEST-1',
            ],
            [
                'academic_year_id' => $academicYear->id,
                'name' => 'Unit Test 1',
                'exam_type_id' => $examType->id,
                'term_id' => $term->id,
                'class_id' => $class->id,
                'section_id' => $section->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->startOfMonth()->addDays(2)->toDateString(),
                'total_marks' => 100,
                'passing_marks' => 35,
                'result_status' => 'draft',
                'created_by' => $admin->id,
            ]
        );

        ExamSubject::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'exam_id' => $exam->id,
                'subject_id' => $subject->id,
            ],
            [
                'max_marks' => 100,
                'passing_marks' => 35,
                'weightage' => 100,
            ]
        );

        $scores = [88, 74, 61];
        foreach ($students->values() as $index => $student) {
            $enrollment = StudentEnrollment::withoutGlobalScopes()->where('school_id', $school->id)
                ->where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->firstOrFail();

            \App\Models\Examination\StudentExamEnrollment::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ],
                [
                    'class_id' => $class->id,
                    'section_id' => $section->id,
                    'roll_no' => $enrollment->roll_number,
                    'status' => 'enrolled',
                ]
            );

            ExamMark::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $school->id,
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                ],
                [
                    'marks_obtained' => $scores[$index] ?? 55,
                    'is_absent' => false,
                    'remarks' => 'Seeded examination mark.',
                    'evaluated_by' => $admin->id,
                    'evaluated_at' => now(),
                ]
            );
        }

        app(ResultComputationService::class)->computeForExam($exam->fresh(['examSubjects.subject', 'studentExamEnrollments.student']), $gradingSystem->id);
    }

    protected function ensureDemoStudents(int $schoolId, int $classId, int $sectionId, int $academicYearId, int $adminId): Collection
    {
        $categoryId = StudentCategory::withoutGlobalScopes()->where('school_id', $schoolId)->value('id');
        $houseId = StudentHouse::withoutGlobalScopes()->where('school_id', $schoolId)->value('id');

        $definitions = [
            [
                'admission_no' => 'ADM-2026-0001',
                'roll_no' => '10A-01',
                'first_name' => 'Riya',
                'last_name' => 'Sharma',
                'email' => 'riya.sharma@student.greenwood.edu',
                'phone' => '7777777777',
                'gender' => 'female',
                'date_of_birth' => '2011-08-14',
            ],
            [
                'admission_no' => 'ADM-2026-0002',
                'roll_no' => '10A-02',
                'first_name' => 'Aarav',
                'last_name' => 'Gupta',
                'email' => 'aarav.gupta@student.greenwood.edu',
                'phone' => '7777777778',
                'gender' => 'male',
                'date_of_birth' => '2011-06-05',
            ],
            [
                'admission_no' => 'ADM-2026-0003',
                'roll_no' => '10A-03',
                'first_name' => 'Maya',
                'last_name' => 'Iyer',
                'email' => 'maya.iyer@student.greenwood.edu',
                'phone' => '7777777779',
                'gender' => 'female',
                'date_of_birth' => '2011-12-11',
            ],
        ];

        return collect($definitions)->map(function (array $studentData) use ($schoolId, $classId, $sectionId, $academicYearId, $adminId, $categoryId, $houseId) {
            $student = Student::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'admission_no' => $studentData['admission_no'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'first_name' => $studentData['first_name'],
                    'middle_name' => null,
                    'last_name' => $studentData['last_name'],
                    'full_name' => $studentData['first_name'].' '.$studentData['last_name'],
                    'roll_no' => $studentData['roll_no'],
                    'preferred_name' => $studentData['first_name'],
                    'email' => $studentData['email'],
                    'phone' => $studentData['phone'],
                    'gender' => $studentData['gender'],
                    'date_of_birth' => $studentData['date_of_birth'],
                    'admission_date' => '2026-04-10',
                    'joining_date' => '2026-04-10',
                    'blood_group' => 'O+',
                    'status' => 'active',
                    'current_status' => 'active',
                    'category_id' => $categoryId,
                    'house_id' => $houseId,
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            StudentEnrollment::withoutGlobalScopes()->updateOrCreate(
                [
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                ],
                [
                    'school_class_id' => $classId,
                    'section_id' => $sectionId,
                    'roll_number' => $studentData['roll_no'],
                    'enrollment_date' => '2026-04-10',
                    'status' => 'enrolled',
                    'is_current' => true,
                    'joined_on' => '2026-04-10',
                ]
            );

            return $student->refresh();
        });
    }
}
