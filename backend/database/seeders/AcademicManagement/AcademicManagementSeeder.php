<?php

namespace Database\Seeders\AcademicManagement;

use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\Curriculum;
use App\Models\AcademicManagement\GradingStructure;
use App\Models\AcademicManagement\HomeworkAssignment;
use App\Models\AcademicManagement\LessonPlan;
use App\Models\AcademicManagement\Subject;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\HR\Staff;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicManagementSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::withoutGlobalScopes()->where('code', 'greenwood')->firstOrFail();
        $admin = User::withoutGlobalScopes()->where('school_id', $school->id)->where('email', 'admin@greenwood.edu')->firstOrFail();
        $teacherStaff = Staff::withoutGlobalScopes()->where('school_id', $school->id)->where('employee_code', 'EMP-0002')->first()
            ?? Staff::withoutGlobalScopes()->where('school_id', $school->id)->where('user_id', $admin->id)->firstOrFail();

        $academicYear = AcademicYear::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'AY-2026-27'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Academic Year 2026-2027',
                'start_date' => '2026-04-01',
                'end_date' => '2027-03-31',
                'is_active' => true,
                'is_current' => true,
                'status' => 'active',
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]
        );

        $term1 = AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'academic_year_id' => $academicYear->id, 'code' => 'T1'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Term 1',
                'start_date' => '2026-04-01',
                'end_date' => '2026-09-30',
                'sequence' => 1,
                'status' => 'active',
            ]
        );

        AcademicTerm::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'academic_year_id' => $academicYear->id, 'code' => 'T2'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Term 2',
                'start_date' => '2026-10-01',
                'end_date' => '2027-03-31',
                'sequence' => 2,
                'status' => 'active',
            ]
        );

        $schoolClass = SchoolClass::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'academic_year_id' => $academicYear->id, 'code' => 'G8'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Grade 8',
                'grade_level' => 8,
                'level_order' => 8,
                'sort_order' => 8,
                'description' => 'Middle school grade 8',
                'status' => 'active',
            ]
        );

        $section = Section::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'school_class_id' => $schoolClass->id, 'code' => 'A'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Section A',
                'capacity' => 35,
                'status' => 'active',
            ]
        );

        $math = Subject::withoutGlobalScopes()->updateOrCreate(
            ['school_id' => $school->id, 'code' => 'MATH'],
            [
                'uuid' => (string) Str::uuid(),
                'name' => 'Mathematics',
                'type' => 'mandatory',
                'description' => 'Core mathematics curriculum',
                'status' => 'active',
            ]
        );

        ClassSubjectAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'subject_id' => $math->id,
            ],
            [
                'is_optional' => false,
                'weekly_periods' => 5,
                'status' => 'active',
            ]
        );

        TeacherAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'subject_id' => $math->id,
                'staff_id' => $teacherStaff->id,
            ],
            [
                'is_class_teacher' => true,
                'status' => 'active',
            ]
        );

        Curriculum::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'subject_id' => $math->id,
                'academic_term_id' => $term1->id,
                'title' => 'Algebra Foundations',
            ],
            [
                'description' => 'Linear expressions, equations, and graphs.',
                'sequence' => 1,
                'learning_outcomes' => 'Students solve and graph linear equations.',
                'status' => 'active',
            ]
        );

        LessonPlan::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'subject_id' => $math->id,
                'staff_id' => $teacherStaff->id,
                'title' => 'Introduction to Linear Equations',
            ],
            [
                'academic_term_id' => $term1->id,
                'topic' => 'Linear Equations',
                'objectives' => 'Understand balancing equations and solving for x.',
                'teaching_method' => 'Interactive board work',
                'planned_date' => '2026-04-15',
                'duration_minutes' => 45,
                'materials_needed' => 'Workbook, projector',
                'notes' => 'Include peer practice',
                'status' => 'published',
            ]
        );

        HomeworkAssignment::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'school_class_id' => $schoolClass->id,
                'section_id' => $section->id,
                'subject_id' => $math->id,
                'staff_id' => $teacherStaff->id,
                'title' => 'Worksheet 1',
            ],
            [
                'academic_term_id' => $term1->id,
                'description' => 'Solve the worksheet on simple linear equations.',
                'assigned_date' => '2026-04-15',
                'due_date' => '2026-04-18',
                'total_marks' => 20,
                'status' => 'active',
            ]
        );

        AcademicCalendarEvent::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'title' => 'Orientation Day',
            ],
            [
                'description' => 'Welcome session for all students and parents.',
                'event_type' => 'orientation',
                'start_datetime' => '2026-04-03 09:00:00',
                'end_datetime' => '2026-04-03 12:00:00',
                'is_holiday' => false,
                'audience_type' => 'all',
                'status' => 'active',
            ]
        );

        $gradingStructure = GradingStructure::withoutGlobalScopes()->updateOrCreate(
            [
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'name' => 'Standard Percentage Scale',
            ],
            [
                'description' => 'Default grading scale for report card generation.',
                'pass_percentage' => 35,
                'status' => 'active',
            ]
        );

        $gradingStructure->scaleItems()->delete();
        $gradingStructure->scaleItems()->createMany([
            ['grade_label' => 'A', 'min_percentage' => 85, 'max_percentage' => 100, 'grade_point' => 4.0, 'remarks' => 'Excellent'],
            ['grade_label' => 'B', 'min_percentage' => 70, 'max_percentage' => 84.99, 'grade_point' => 3.0, 'remarks' => 'Good'],
            ['grade_label' => 'C', 'min_percentage' => 50, 'max_percentage' => 69.99, 'grade_point' => 2.0, 'remarks' => 'Average'],
            ['grade_label' => 'D', 'min_percentage' => 35, 'max_percentage' => 49.99, 'grade_point' => 1.0, 'remarks' => 'Pass'],
            ['grade_label' => 'F', 'min_percentage' => 0, 'max_percentage' => 34.99, 'grade_point' => 0, 'remarks' => 'Fail'],
        ]);
    }
}
