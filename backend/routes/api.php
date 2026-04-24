<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\SIS\AdmissionController;
use App\Http\Controllers\Api\V1\SIS\StudentCategoryController;
use App\Http\Controllers\Api\V1\SIS\StudentDocumentController;
use App\Http\Controllers\Api\V1\SIS\StudentEnrollmentController;
use App\Http\Controllers\Api\V1\SIS\StudentHouseController;
use App\Http\Controllers\Api\V1\SIS\StudentMedicalRecordController;
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
use App\Http\Controllers\Api\V1\HR\DepartmentController;
use App\Http\Controllers\Api\V1\HR\DesignationController;
use App\Http\Controllers\Api\V1\HR\LeaveBalanceController;
use App\Http\Controllers\Api\V1\HR\LeaveTypeController;
use App\Http\Controllers\Api\V1\HR\PayrollRunController;
use App\Http\Controllers\Api\V1\HR\SalaryComponentController;
use App\Http\Controllers\Api\V1\HR\SalaryStructureController;
use App\Http\Controllers\Api\V1\HR\StaffController;
use App\Http\Controllers\Api\V1\HR\StaffAttendanceController;
use App\Http\Controllers\Api\V1\HR\StaffBankDetailController;
use App\Http\Controllers\Api\V1\HR\StaffDocumentController;
use App\Http\Controllers\Api\V1\HR\StaffEmergencyContactController;
use App\Http\Controllers\Api\V1\HR\StaffLeaveApplicationController;
use App\Http\Controllers\Api\V1\HR\StaffNoteController;
use App\Http\Controllers\Api\V1\HR\StaffPayslipController;
use App\Http\Controllers\Api\V1\HR\StaffQualificationController;
use App\Http\Controllers\Api\V1\HR\StaffStatusHistoryController;
use App\Http\Controllers\Api\V1\HR\StaffWorkExperienceController;
use App\Http\Controllers\Api\V1\MasterData\AcademicYearController as MasterDataAcademicYearController;
use App\Http\Controllers\Api\V1\MasterData\GuardianController;
use App\Http\Controllers\Api\V1\MasterData\SectionController as MasterDataSectionController;
use App\Http\Controllers\Api\V1\MasterData\SchoolClassController as MasterDataSchoolClassController;
use App\Http\Controllers\Api\V1\SIS\StudentController;
use App\Http\Controllers\Api\V1\SIS\StudentNoteController;
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
        Route::get('/students/{student}/guardians', [StudentController::class, 'guardians'])->middleware('permission:students.view');
        Route::get('/students/{student}/enrollments', [StudentEnrollmentController::class, 'studentEnrollments'])->middleware('permission:students.view');
        Route::get('/students/{student}/documents', [StudentDocumentController::class, 'studentDocuments'])->middleware('permission:students.view');
        Route::get('/students/{student}/medical', [StudentMedicalRecordController::class, 'studentMedical'])->middleware('permission:students.view');
        Route::get('/students/{student}/status-history', [StudentController::class, 'statusHistory'])->middleware('permission:students.view');
        Route::get('/students/{student}/notes', [StudentNoteController::class, 'studentNotes'])->middleware('permission:students.update');
        Route::post('/students/{student}/notes', [StudentNoteController::class, 'store'])->middleware('permission:students.update');
        Route::put('/students/{student}/medical', [StudentMedicalRecordController::class, 'upsertForStudent'])->middleware('permission:students.medical.manage');
        Route::post('/students/{student}/promote', [StudentController::class, 'promote'])->middleware('permission:students.update');
        Route::post('/students/{student}/transfer-section', [StudentController::class, 'transferSection'])->middleware('permission:students.update');
        Route::post('/students/{student}/withdraw', [StudentController::class, 'withdraw'])->middleware('permission:students.update');
        Route::post('/students/{student}/graduate', [StudentController::class, 'graduate'])->middleware('permission:students.update');
        Route::post('/students/{student}/suspend', [StudentController::class, 'suspend'])->middleware('permission:students.update');
        Route::post('/students/{student}/reactivate', [StudentController::class, 'reactivate'])->middleware('permission:students.update');
        Route::post('/students/{student}/enroll', [StudentEnrollmentController::class, 'enroll'])->middleware('permission:students.create');
        Route::post('/students/{student}/assign-guardian', [StudentController::class, 'assignGuardian'])->middleware('permission:students.update');
        Route::delete('/students/{student}/remove-guardian/{guardianId}', [StudentController::class, 'removeGuardian'])->middleware('permission:students.update');
        Route::post('/students/{student}/documents', [StudentDocumentController::class, 'store'])->middleware('permission:students.documents.upload');

        Route::put('/student-notes/{studentNote}', [StudentNoteController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/student-notes/{studentNote}', [StudentNoteController::class, 'destroy'])->middleware('permission:students.update');

        Route::get('/student-enrollments', [StudentEnrollmentController::class, 'index'])->middleware('permission:students.view');
        Route::post('/student-enrollments', [StudentEnrollmentController::class, 'store'])->middleware('permission:students.create');
        Route::get('/student-enrollments/{studentEnrollment}', [StudentEnrollmentController::class, 'show'])->middleware('permission:students.view');
        Route::put('/student-enrollments/{studentEnrollment}', [StudentEnrollmentController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/student-enrollments/{studentEnrollment}', [StudentEnrollmentController::class, 'destroy'])->middleware('permission:students.delete');

        Route::get('/student-documents', [StudentDocumentController::class, 'index'])->middleware('permission:students.view');
        Route::get('/student-documents/{studentDocument}', [StudentDocumentController::class, 'show'])->middleware('permission:students.view');
        Route::put('/student-documents/{studentDocument}', [StudentDocumentController::class, 'update'])->middleware('permission:students.documents.upload');
        Route::delete('/student-documents/{studentDocument}', [StudentDocumentController::class, 'destroy'])->middleware('permission:students.documents.upload');

        Route::get('/student-medical-records', [StudentMedicalRecordController::class, 'index'])->middleware('permission:students.view');
        Route::post('/student-medical-records', [StudentMedicalRecordController::class, 'store'])->middleware('permission:students.medical.manage');
        Route::get('/student-medical-records/{studentMedicalRecord}', [StudentMedicalRecordController::class, 'show'])->middleware('permission:students.view');
        Route::put('/student-medical-records/{studentMedicalRecord}', [StudentMedicalRecordController::class, 'update'])->middleware('permission:students.medical.manage');
        Route::delete('/student-medical-records/{studentMedicalRecord}', [StudentMedicalRecordController::class, 'destroy'])->middleware('permission:students.medical.manage');

        Route::get('/student-admissions', [AdmissionController::class, 'index'])->middleware('permission:students.view');
        Route::post('/student-admissions', [AdmissionController::class, 'store'])->middleware('permission:students.create');
        Route::get('/student-admissions/{studentAdmission}', [AdmissionController::class, 'show'])->middleware('permission:students.view');
        Route::put('/student-admissions/{studentAdmission}', [AdmissionController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/student-admissions/{studentAdmission}', [AdmissionController::class, 'destroy'])->middleware('permission:students.delete');
        Route::post('/student-admissions/{studentAdmission}/submit', [AdmissionController::class, 'submit'])->middleware('permission:students.update');
        Route::post('/student-admissions/{studentAdmission}/review', [AdmissionController::class, 'review'])->middleware('permission:students.update');
        Route::post('/student-admissions/{studentAdmission}/approve', [AdmissionController::class, 'approve'])->middleware('permission:students.update');
        Route::post('/student-admissions/{studentAdmission}/reject', [AdmissionController::class, 'reject'])->middleware('permission:students.update');
        Route::post('/student-admissions/{studentAdmission}/waitlist', [AdmissionController::class, 'waitlist'])->middleware('permission:students.update');
        Route::post('/student-admissions/{studentAdmission}/convert-to-student', [AdmissionController::class, 'convertToStudent'])->middleware('permission:students.create');

        Route::get('/guardians', [GuardianController::class, 'index'])->middleware('permission:students.view');
        Route::post('/guardians', [GuardianController::class, 'store'])->middleware('permission:students.create');
        Route::put('/guardians/{guardian}', [GuardianController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])->middleware('permission:students.delete');

        Route::get('/student-categories', [StudentCategoryController::class, 'index'])->middleware('permission:students.view');
        Route::post('/student-categories', [StudentCategoryController::class, 'store'])->middleware('permission:students.create');
        Route::put('/student-categories/{studentCategory}', [StudentCategoryController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/student-categories/{studentCategory}', [StudentCategoryController::class, 'destroy'])->middleware('permission:students.delete');

        Route::get('/student-houses', [StudentHouseController::class, 'index'])->middleware('permission:students.view');
        Route::post('/student-houses', [StudentHouseController::class, 'store'])->middleware('permission:students.create');
        Route::put('/student-houses/{studentHouse}', [StudentHouseController::class, 'update'])->middleware('permission:students.update');
        Route::delete('/student-houses/{studentHouse}', [StudentHouseController::class, 'destroy'])->middleware('permission:students.delete');

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

        Route::prefix('hr')->group(function (): void {
            Route::get('/departments', [DepartmentController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/departments', [DepartmentController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/departments/{department}', [DepartmentController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/departments/{department}', [DepartmentController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/designations', [DesignationController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/designations', [DesignationController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/designations/{designation}', [DesignationController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/designations/{designation}', [DesignationController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/designations/{designation}', [DesignationController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/leave-types', [LeaveTypeController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/leave-types', [LeaveTypeController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/leave-types/{leaveType}', [LeaveTypeController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/leave-types/{leaveType}', [LeaveTypeController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/leave-applications', [StaffLeaveApplicationController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/leave-applications', [StaffLeaveApplicationController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/leave-applications/{leaveApplication}', [StaffLeaveApplicationController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/leave-applications/{leaveApplication}', [StaffLeaveApplicationController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/leave-applications/{leaveApplication}', [StaffLeaveApplicationController::class, 'destroy'])->middleware('permission:hr.manage');
            Route::post('/leave-applications/{leaveApplication}/submit', [StaffLeaveApplicationController::class, 'submit'])->middleware('permission:hr.manage');
            Route::post('/leave-applications/{leaveApplication}/approve', [StaffLeaveApplicationController::class, 'approve'])->middleware('permission:hr.manage');
            Route::post('/leave-applications/{leaveApplication}/reject', [StaffLeaveApplicationController::class, 'reject'])->middleware('permission:hr.manage');
            Route::post('/leave-applications/{leaveApplication}/cancel', [StaffLeaveApplicationController::class, 'cancel'])->middleware('permission:hr.manage');

            Route::get('/leave-balances', [LeaveBalanceController::class, 'index'])->middleware('permission:hr.view');

            Route::get('/salary-components', [SalaryComponentController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/salary-components', [SalaryComponentController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/salary-components/{salaryComponent}', [SalaryComponentController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/salary-components/{salaryComponent}', [SalaryComponentController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/salary-components/{salaryComponent}', [SalaryComponentController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/salary-structures', [SalaryStructureController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/salary-structures', [SalaryStructureController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/salary-structures/{salaryStructure}', [SalaryStructureController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/salary-structures/{salaryStructure}', [SalaryStructureController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/salary-structures/{salaryStructure}', [SalaryStructureController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/payroll-runs', [PayrollRunController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/payroll-runs', [PayrollRunController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/payroll-runs/{payrollRun}', [PayrollRunController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/payroll-runs/{payrollRun}', [PayrollRunController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/payroll-runs/{payrollRun}', [PayrollRunController::class, 'destroy'])->middleware('permission:hr.manage');
            Route::post('/payroll-runs/{payrollRun}/process', [PayrollRunController::class, 'process'])->middleware('permission:hr.manage');
            Route::post('/payroll-runs/{payrollRun}/finalize', [PayrollRunController::class, 'finalize'])->middleware('permission:hr.manage');
            Route::post('/payroll-runs/{payrollRun}/mark-paid', [PayrollRunController::class, 'markPaid'])->middleware('permission:hr.manage');

            Route::get('/payslips', [StaffPayslipController::class, 'index'])->middleware('permission:hr.view');
            Route::get('/payslips/{payslip}', [StaffPayslipController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/payslips/{payslip}', [StaffPayslipController::class, 'update'])->middleware('permission:hr.manage');

            Route::get('/staff', [StaffController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/staff', [StaffController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}', [StaffController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/staff/{staff}', [StaffController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->middleware('permission:hr.manage');
            Route::post('/staff/{staff}/activate', [StaffController::class, 'activate'])->middleware('permission:hr.manage');
            Route::post('/staff/{staff}/suspend', [StaffController::class, 'suspend'])->middleware('permission:hr.manage');
            Route::post('/staff/{staff}/resign', [StaffController::class, 'resign'])->middleware('permission:hr.manage');
            Route::post('/staff/{staff}/terminate', [StaffController::class, 'terminate'])->middleware('permission:hr.manage');
            Route::post('/staff/{staff}/retire', [StaffController::class, 'retire'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/documents', [StaffDocumentController::class, 'staffDocuments'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/upload-document', [StaffDocumentController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/attendance', [StaffAttendanceController::class, 'staffAttendance'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/attendance', [StaffAttendanceController::class, 'storeForStaff'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/leave-balance', [LeaveBalanceController::class, 'staffBalance'])->middleware('permission:hr.view');
            Route::get('/staff/{staff}/bank-details', [StaffBankDetailController::class, 'staffBankDetails'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/bank-details', [StaffBankDetailController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/emergency-contacts', [StaffEmergencyContactController::class, 'staffContacts'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/emergency-contacts', [StaffEmergencyContactController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/status-history', [StaffStatusHistoryController::class, 'staffHistory'])->middleware('permission:hr.view');
            Route::get('/staff/{staff}/notes', [StaffNoteController::class, 'staffNotes'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/notes', [StaffNoteController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/qualifications', [StaffQualificationController::class, 'staffQualifications'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/qualifications', [StaffQualificationController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff/{staff}/experiences', [StaffWorkExperienceController::class, 'staffExperiences'])->middleware('permission:hr.view');
            Route::post('/staff/{staff}/experiences', [StaffWorkExperienceController::class, 'store'])->middleware('permission:hr.manage');

            Route::get('/staff-bank-details', [StaffBankDetailController::class, 'index'])->middleware('permission:hr.view');
            Route::put('/staff-bank-details/{staffBankDetail}', [StaffBankDetailController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-bank-details/{staffBankDetail}', [StaffBankDetailController::class, 'destroy'])->middleware('permission:hr.manage');
            Route::get('/staff-documents', [StaffDocumentController::class, 'index'])->middleware('permission:hr.view');
            Route::get('/staff-documents/{staffDocument}', [StaffDocumentController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/staff-documents/{staffDocument}', [StaffDocumentController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-documents/{staffDocument}', [StaffDocumentController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/staff-attendance', [StaffAttendanceController::class, 'index'])->middleware('permission:hr.view');
            Route::post('/staff-attendance', [StaffAttendanceController::class, 'store'])->middleware('permission:hr.manage');
            Route::get('/staff-attendance/{staffAttendance}', [StaffAttendanceController::class, 'show'])->middleware('permission:hr.view');
            Route::put('/staff-attendance/{staffAttendance}', [StaffAttendanceController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-attendance/{staffAttendance}', [StaffAttendanceController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/staff-emergency-contacts', [StaffEmergencyContactController::class, 'index'])->middleware('permission:hr.view');
            Route::put('/staff-emergency-contacts/{staffEmergencyContact}', [StaffEmergencyContactController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-emergency-contacts/{staffEmergencyContact}', [StaffEmergencyContactController::class, 'destroy'])->middleware('permission:hr.manage');
            Route::get('/staff-notes', [StaffNoteController::class, 'index'])->middleware('permission:hr.view');
            Route::put('/staff-notes/{staffNote}', [StaffNoteController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-notes/{staffNote}', [StaffNoteController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/staff-qualifications', [StaffQualificationController::class, 'index'])->middleware('permission:hr.view');
            Route::put('/staff-qualifications/{staffQualification}', [StaffQualificationController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-qualifications/{staffQualification}', [StaffQualificationController::class, 'destroy'])->middleware('permission:hr.manage');

            Route::get('/staff-experiences', [StaffWorkExperienceController::class, 'index'])->middleware('permission:hr.view');
            Route::put('/staff-experiences/{staffWorkExperience}', [StaffWorkExperienceController::class, 'update'])->middleware('permission:hr.manage');
            Route::delete('/staff-experiences/{staffWorkExperience}', [StaffWorkExperienceController::class, 'destroy'])->middleware('permission:hr.manage');
        });
    });
});
