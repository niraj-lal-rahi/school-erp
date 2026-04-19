<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\AcademicManagement\AcademicCalendarEventController;
use App\Http\Controllers\Api\V1\AcademicManagement\AcademicManagementOptionsController;
use App\Http\Controllers\Api\V1\AcademicManagement\AcademicTermController;
use App\Http\Controllers\Api\V1\AcademicManagement\AcademicYearController;
use App\Http\Controllers\Api\V1\AcademicManagement\ClassSubjectAssignmentController;
use App\Http\Controllers\Api\V1\AcademicManagement\CurriculumController;
use App\Http\Controllers\Api\V1\AcademicManagement\GradingStructureController;
use App\Http\Controllers\Api\V1\AcademicManagement\HomeworkAssignmentController;
use App\Http\Controllers\Api\V1\AcademicManagement\LessonPlanController;
use App\Http\Controllers\Api\V1\AcademicManagement\SchoolClassController as AcademicManagementSchoolClassController;
use App\Http\Controllers\Api\V1\AcademicManagement\SectionController as AcademicManagementSectionController;
use App\Http\Controllers\Api\V1\AcademicManagement\SubjectController;
use App\Http\Controllers\Api\V1\AcademicManagement\TeacherAssignmentController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\MasterData\AcademicYearController as MasterDataAcademicYearController;
use App\Http\Controllers\Api\V1\MasterData\GuardianController;
use App\Http\Controllers\Api\V1\MasterData\SectionController as MasterDataSectionController;
use App\Http\Controllers\Api\V1\MasterData\SchoolClassController as MasterDataSchoolClassController;
use App\Http\Controllers\Api\V1\SIS\StudentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    Route::middleware(['auth:api', 'tenant.resolve'])->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard/overview', [DashboardController::class, 'overview'])->middleware('permission:students.view');

        Route::get('/students', [StudentController::class, 'index'])->middleware('permission:students.view');
        Route::post('/students', [StudentController::class, 'store'])->middleware('permission:students.create');
        Route::get('/students/{student}', [StudentController::class, 'show'])->middleware('permission:students.view');
        Route::put('/students/{student}', [StudentController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->middleware('permission:students.delete');
        Route::post('/students/{student}/documents', [StudentController::class, 'uploadDocument'])->middleware('permission:students.documents.upload');

        Route::get('/guardians', [GuardianController::class, 'index'])->middleware('permission:students.view');
        Route::post('/guardians', [GuardianController::class, 'store'])->middleware('permission:students.create');
        Route::put('/guardians/{guardian}', [GuardianController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])->middleware('permission:students.delete');

        Route::get('/academic-years', [MasterDataAcademicYearController::class, 'index'])->middleware('permission:students.view');
        Route::post('/academic-years', [MasterDataAcademicYearController::class, 'store'])->middleware('permission:students.create');

        Route::get('/classes', [MasterDataSchoolClassController::class, 'index'])->middleware('permission:students.view');
        Route::post('/classes', [MasterDataSchoolClassController::class, 'store'])->middleware('permission:students.create');

        Route::get('/sections', [MasterDataSectionController::class, 'index'])->middleware('permission:students.view');
        Route::post('/sections', [MasterDataSectionController::class, 'store'])->middleware('permission:students.create');
        Route::put('/sections/{section}', [MasterDataSectionController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/sections/{section}', [MasterDataSectionController::class, 'destroy'])->middleware('permission:students.delete');

        Route::prefix('academic-management')->group(function (): void {
            Route::get('/options', [AcademicManagementOptionsController::class, 'index'])->middleware('permission:academic-management.view');

            Route::get('/academic-years', [AcademicYearController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/academic-years', [AcademicYearController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/academic-years/{academicYear}', [AcademicYearController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/academic-years/{academicYear}', [AcademicYearController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::patch('/academic-years/{academicYear}/status', [AcademicYearController::class, 'updateStatus'])->middleware('permission:academic-management.manage');
            Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/terms', [AcademicTermController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/terms', [AcademicTermController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/terms/{term}', [AcademicTermController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/terms/{term}', [AcademicTermController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/terms/{term}', [AcademicTermController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/classes', [AcademicManagementSchoolClassController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/classes', [AcademicManagementSchoolClassController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/classes/{schoolClass}', [AcademicManagementSchoolClassController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/classes/{schoolClass}', [AcademicManagementSchoolClassController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/classes/{schoolClass}', [AcademicManagementSchoolClassController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/sections', [AcademicManagementSectionController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/sections', [AcademicManagementSectionController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/sections/{section}', [AcademicManagementSectionController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/sections/{section}', [AcademicManagementSectionController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/sections/{section}', [AcademicManagementSectionController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/subjects', [SubjectController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/subjects', [SubjectController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/class-subjects', [ClassSubjectAssignmentController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/class-subjects', [ClassSubjectAssignmentController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/class-subjects/{classSubject}', [ClassSubjectAssignmentController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/class-subjects/{classSubject}', [ClassSubjectAssignmentController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/class-subjects/{classSubject}', [ClassSubjectAssignmentController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/teacher-assignments', [TeacherAssignmentController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/teacher-assignments', [TeacherAssignmentController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/curriculum', [CurriculumController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/curriculum', [CurriculumController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/curriculum/{curriculum}', [CurriculumController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/curriculum/{curriculum}', [CurriculumController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/curriculum/{curriculum}', [CurriculumController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/lesson-plans', [LessonPlanController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/lesson-plans', [LessonPlanController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/assignments', [HomeworkAssignmentController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/assignments', [HomeworkAssignmentController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/assignments/{assignment}', [HomeworkAssignmentController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/assignments/{assignment}', [HomeworkAssignmentController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/assignments/{assignment}', [HomeworkAssignmentController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/academic-calendar', [AcademicCalendarEventController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/academic-calendar', [AcademicCalendarEventController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/academic-calendar/{academicCalendar}', [AcademicCalendarEventController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/academic-calendar/{academicCalendar}', [AcademicCalendarEventController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/academic-calendar/{academicCalendar}', [AcademicCalendarEventController::class, 'destroy'])->middleware('permission:academic-management.manage');

            Route::get('/grading-structures', [GradingStructureController::class, 'index'])->middleware('permission:academic-management.view');
            Route::post('/grading-structures', [GradingStructureController::class, 'store'])->middleware('permission:academic-management.manage');
            Route::get('/grading-structures/{gradingStructure}', [GradingStructureController::class, 'show'])->middleware('permission:academic-management.view');
            Route::put('/grading-structures/{gradingStructure}', [GradingStructureController::class, 'update'])->middleware('permission:academic-management.manage');
            Route::delete('/grading-structures/{gradingStructure}', [GradingStructureController::class, 'destroy'])->middleware('permission:academic-management.manage');
        });
    });
});
