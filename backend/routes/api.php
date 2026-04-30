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
use App\Http\Controllers\Api\V1\Attendance\AttendancePeriodController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceCorrectionController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceHolidayController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceImportController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceReportController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceSummaryController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceStatusTypeController;
use App\Http\Controllers\Api\V1\Attendance\BiometricLogController;
use App\Http\Controllers\Api\V1\Attendance\StaffAttendanceController as AttendanceStaffAttendanceController;
use App\Http\Controllers\Api\V1\Attendance\StudentAttendanceRecordController;
use App\Http\Controllers\Api\V1\Attendance\StudentAttendanceSessionController;
use App\Http\Controllers\Api\V1\Communication\AnnouncementController as CommunicationAnnouncementController;
use App\Http\Controllers\Api\V1\Communication\CommunicationChannelController;
use App\Http\Controllers\Api\V1\Communication\CommunicationGroupController;
use App\Http\Controllers\Api\V1\Communication\CommunicationMessageController;
use App\Http\Controllers\Api\V1\Communication\ConversationController;
use App\Http\Controllers\Api\V1\Communication\MessageTemplateController;
use App\Http\Controllers\Api\V1\Communication\NotificationController as CommunicationNotificationController;
use App\Http\Controllers\Api\V1\Communication\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\Communication\ScheduledMessageController;
use App\Http\Controllers\Api\V1\Dashboard\DashboardController;
use App\Http\Controllers\Api\V1\Examination\ExamController as ExaminationExamController;
use App\Http\Controllers\Api\V1\Examination\ExamMarkController;
use App\Http\Controllers\Api\V1\Examination\ExamSubjectController;
use App\Http\Controllers\Api\V1\Examination\ExamTypeController;
use App\Http\Controllers\Api\V1\Examination\GradingSystemController;
use App\Http\Controllers\Api\V1\Examination\ResultController;
use App\Http\Controllers\Api\V1\Examination\ResultPublicationController;
use App\Http\Controllers\Api\V1\Examination\RevaluationController;
use App\Http\Controllers\Api\V1\Finance\FeeCategoryController;
use App\Http\Controllers\Api\V1\Finance\ExpenseCategoryController;
use App\Http\Controllers\Api\V1\Finance\ExpenseController;
use App\Http\Controllers\Api\V1\Finance\FinanceReportController;
use App\Http\Controllers\Api\V1\Finance\DiscountTypeController;
use App\Http\Controllers\Api\V1\Finance\FeeHeadController;
use App\Http\Controllers\Api\V1\Finance\FeeInstallmentController;
use App\Http\Controllers\Api\V1\Finance\FeeInvoiceController;
use App\Http\Controllers\Api\V1\Finance\FineRuleController;
use App\Http\Controllers\Api\V1\Finance\LedgerAccountController;
use App\Http\Controllers\Api\V1\Finance\LedgerEntryController;
use App\Http\Controllers\Api\V1\Finance\PaymentController;
use App\Http\Controllers\Api\V1\Finance\ReceiptController;
use App\Http\Controllers\Api\V1\Finance\RefundController;
use App\Http\Controllers\Api\V1\Finance\FeeStructureController;
use App\Http\Controllers\Api\V1\Finance\StudentDiscountController;
use App\Http\Controllers\Api\V1\Finance\StudentFeeAssignmentController;
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
use App\Http\Controllers\Api\V1\Reports\DashboardController as ReportsDashboardController;
use App\Http\Controllers\Api\V1\Reports\ReportDefinitionController;
use App\Http\Controllers\Api\V1\Reports\ReportExportController;
use App\Http\Controllers\Api\V1\Reports\ReportRunController;
use App\Http\Controllers\Api\V1\Reports\ReportScheduleController;
use App\Http\Controllers\Api\V1\Portal\PortalContextController;
use App\Http\Controllers\Api\V1\Portal\PortalDashboardController;
use App\Http\Controllers\Api\V1\Portal\PortalNotificationController;
use App\Http\Controllers\Api\V1\Portal\PortalProfileController;
use App\Http\Controllers\Api\V1\Portal\PortalStudentController;
use App\Http\Controllers\Api\V1\SIS\StudentController;
use App\Http\Controllers\Api\V1\SIS\StudentNoteController;
use App\Http\Controllers\Api\V1\Timetable\TimetableEntryController;
use App\Http\Controllers\Api\V1\Timetable\TimetableOptionsController;
use App\Http\Controllers\Api\V1\Timetable\TimetablePeriodController;
use App\Http\Controllers\Api\V1\Timetable\TimetablePublishLogController;
use App\Http\Controllers\Api\V1\Timetable\TimetableRoomController;
use App\Http\Controllers\Api\V1\Timetable\TimetableScheduleExceptionController;
use App\Http\Controllers\Api\V1\Timetable\TimetableSubstitutionController;
use App\Http\Controllers\Api\V1\Timetable\TimetableVersionController;
use App\Http\Controllers\Api\V1\Timetable\TimetableViewController;
use App\Http\Controllers\Api\V1\Transport\AllocationController;
use App\Http\Controllers\Api\V1\Transport\DriverController;
use App\Http\Controllers\Api\V1\Transport\FuelController;
use App\Http\Controllers\Api\V1\Transport\GpsController;
use App\Http\Controllers\Api\V1\Transport\MaintenanceController;
use App\Http\Controllers\Api\V1\Transport\RouteController;
use App\Http\Controllers\Api\V1\Transport\RouteStopController;
use App\Http\Controllers\Api\V1\Transport\TripController;
use App\Http\Controllers\Api\V1\Transport\VehicleController;
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

        Route::prefix('finance')->group(function (): void {
            Route::get('/fee-categories', [FeeCategoryController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/fee-categories', [FeeCategoryController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/fee-categories/{feeCategory}', [FeeCategoryController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/fee-categories/{feeCategory}', [FeeCategoryController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/fee-categories/{feeCategory}', [FeeCategoryController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/fee-heads', [FeeHeadController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/fee-heads', [FeeHeadController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/fee-heads/{feeHead}', [FeeHeadController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/fee-heads/{feeHead}', [FeeHeadController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/fee-heads/{feeHead}', [FeeHeadController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/discount-types', [DiscountTypeController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/discount-types', [DiscountTypeController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/discount-types/{discountType}', [DiscountTypeController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/discount-types/{discountType}', [DiscountTypeController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/discount-types/{discountType}', [DiscountTypeController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/student-discounts', [StudentDiscountController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/student-discounts', [StudentDiscountController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/student-discounts/{studentDiscount}', [StudentDiscountController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/student-discounts/{studentDiscount}', [StudentDiscountController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/student-discounts/{studentDiscount}', [StudentDiscountController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/student-discounts/{studentDiscount}/approve', [StudentDiscountController::class, 'approve'])->middleware('permission:finance.manage');
            Route::post('/student-discounts/{studentDiscount}/reject', [StudentDiscountController::class, 'reject'])->middleware('permission:finance.manage');

            Route::get('/fine-rules', [FineRuleController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/fine-rules', [FineRuleController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/fine-rules/{fineRule}', [FineRuleController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/fine-rules/{fineRule}', [FineRuleController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/fine-rules/{fineRule}', [FineRuleController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/fee-structures', [FeeStructureController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/fee-structures', [FeeStructureController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/fee-structures/{feeStructure}', [FeeStructureController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/fee-structures/{feeStructure}', [FeeStructureController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/fee-structures/{feeStructure}', [FeeStructureController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/student-fee-assignments', [StudentFeeAssignmentController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/student-fee-assignments', [StudentFeeAssignmentController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/student-fee-assignments/{studentFeeAssignment}', [StudentFeeAssignmentController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/student-fee-assignments/{studentFeeAssignment}', [StudentFeeAssignmentController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/student-fee-assignments/{studentFeeAssignment}', [StudentFeeAssignmentController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/students/{student}/assign-fee-structure', [StudentFeeAssignmentController::class, 'assignFeeStructure'])->middleware('permission:finance.manage');

            Route::get('/fee-installments', [FeeInstallmentController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/fee-installments', [FeeInstallmentController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/fee-installments/{feeInstallment}', [FeeInstallmentController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/fee-installments/{feeInstallment}', [FeeInstallmentController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/fee-installments/{feeInstallment}', [FeeInstallmentController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/student-fee-assignments/{studentFeeAssignment}/generate-installments', [FeeInstallmentController::class, 'generate'])->middleware('permission:finance.manage');

            Route::get('/invoices', [FeeInvoiceController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/invoices', [FeeInvoiceController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/invoices/{feeInvoice}', [FeeInvoiceController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/invoices/{feeInvoice}', [FeeInvoiceController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/invoices/{feeInvoice}', [FeeInvoiceController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/invoices/{feeInvoice}/issue', [FeeInvoiceController::class, 'issue'])->middleware('permission:finance.manage');
            Route::post('/invoices/{feeInvoice}/cancel', [FeeInvoiceController::class, 'cancel'])->middleware('permission:finance.manage');
            Route::post('/invoices/{feeInvoice}/apply-discount', [FeeInvoiceController::class, 'applyDiscount'])->middleware('permission:finance.manage');
            Route::post('/invoices/{feeInvoice}/apply-fine', [FeeInvoiceController::class, 'applyFine'])->middleware('permission:finance.manage');

            Route::get('/payments', [PaymentController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/payments', [PaymentController::class, 'store'])->middleware('permission:finance.manage');
            Route::post('/payments/collect', [PaymentController::class, 'collect'])->middleware('permission:finance.manage');
            Route::get('/payments/{payment}', [PaymentController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/payments/{payment}', [PaymentController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/payments/{payment}/confirm', [PaymentController::class, 'confirm'])->middleware('permission:finance.manage');
            Route::post('/payments/{payment}/fail', [PaymentController::class, 'fail'])->middleware('permission:finance.manage');

            Route::get('/receipts', [ReceiptController::class, 'index'])->middleware('permission:finance.view');
            Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->middleware('permission:finance.view');
            Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->middleware('permission:finance.view');

            Route::get('/refunds', [RefundController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/refunds', [RefundController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/refunds/{refund}', [RefundController::class, 'show'])->middleware('permission:finance.view');
            Route::delete('/refunds/{refund}', [RefundController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/refunds/{refund}/approve', [RefundController::class, 'approve'])->middleware('permission:finance.manage');
            Route::post('/refunds/{refund}/process', [RefundController::class, 'process'])->middleware('permission:finance.manage');

            Route::get('/expenses', [ExpenseController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/expenses/{expense}', [ExpenseController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->middleware('permission:finance.manage');
            Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve'])->middleware('permission:finance.manage');
            Route::post('/expenses/{expense}/mark-paid', [ExpenseController::class, 'markPaid'])->middleware('permission:finance.manage');

            Route::get('/ledger-accounts', [LedgerAccountController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/ledger-accounts', [LedgerAccountController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/ledger-accounts/{ledgerAccount}', [LedgerAccountController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/ledger-accounts/{ledgerAccount}', [LedgerAccountController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/ledger-accounts/{ledgerAccount}', [LedgerAccountController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::get('/ledger-entries', [LedgerEntryController::class, 'index'])->middleware('permission:finance.view');
            Route::post('/ledger-entries', [LedgerEntryController::class, 'store'])->middleware('permission:finance.manage');
            Route::get('/ledger-entries/{ledgerEntry}', [LedgerEntryController::class, 'show'])->middleware('permission:finance.view');
            Route::put('/ledger-entries/{ledgerEntry}', [LedgerEntryController::class, 'update'])->middleware('permission:finance.manage');
            Route::delete('/ledger-entries/{ledgerEntry}', [LedgerEntryController::class, 'destroy'])->middleware('permission:finance.manage');

            Route::prefix('/reports')->group(function (): void {
                Route::get('/fee-collection', [FinanceReportController::class, 'feeCollection'])->middleware('permission:finance.view');
                Route::get('/outstanding-fees', [FinanceReportController::class, 'outstandingFees'])->middleware('permission:finance.view');
                Route::get('/student-ledger', [FinanceReportController::class, 'studentLedger'])->middleware('permission:finance.view');
                Route::get('/daily-collection', [FinanceReportController::class, 'dailyCollection'])->middleware('permission:finance.view');
                Route::get('/expense-summary', [FinanceReportController::class, 'expenseSummary'])->middleware('permission:finance.view');
                Route::get('/income-vs-expense', [FinanceReportController::class, 'incomeVsExpense'])->middleware('permission:finance.view');
            });
        });

        Route::prefix('timetable')->group(function (): void {
            Route::get('/options', [TimetableOptionsController::class, 'index'])->middleware('permission:timetable.view');

            Route::get('/periods', [TimetablePeriodController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/periods', [TimetablePeriodController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/periods/{period}', [TimetablePeriodController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/periods/{period}', [TimetablePeriodController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/periods/{period}', [TimetablePeriodController::class, 'destroy'])->middleware('permission:timetable.manage');

            Route::get('/rooms', [TimetableRoomController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/rooms', [TimetableRoomController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/rooms/{room}', [TimetableRoomController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/rooms/{room}', [TimetableRoomController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/rooms/{room}', [TimetableRoomController::class, 'destroy'])->middleware('permission:timetable.manage');

            Route::get('/versions', [TimetableVersionController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/versions', [TimetableVersionController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/versions/{version}', [TimetableVersionController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/versions/{version}', [TimetableVersionController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/versions/{version}', [TimetableVersionController::class, 'destroy'])->middleware('permission:timetable.manage');
            Route::post('/versions/{version}/publish', [TimetableVersionController::class, 'publish'])->middleware('permission:timetable.manage');
            Route::post('/versions/{version}/archive', [TimetableVersionController::class, 'archive'])->middleware('permission:timetable.manage');
            Route::post('/versions/{version}/duplicate', [TimetableVersionController::class, 'duplicate'])->middleware('permission:timetable.manage');

            Route::get('/entries', [TimetableEntryController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/entries', [TimetableEntryController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/entries/{entry}', [TimetableEntryController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/entries/{entry}', [TimetableEntryController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/entries/{entry}', [TimetableEntryController::class, 'destroy'])->middleware('permission:timetable.manage');
            Route::post('/entries/bulk-create', [TimetableEntryController::class, 'bulkCreate'])->middleware('permission:timetable.manage');
            Route::post('/entries/check-conflicts', [TimetableEntryController::class, 'checkConflicts'])->middleware('permission:timetable.manage');

            Route::get('/substitutions', [TimetableSubstitutionController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/substitutions', [TimetableSubstitutionController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/substitutions/{substitution}', [TimetableSubstitutionController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/substitutions/{substitution}', [TimetableSubstitutionController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/substitutions/{substitution}', [TimetableSubstitutionController::class, 'destroy'])->middleware('permission:timetable.manage');
            Route::post('/substitutions/{substitution}/approve', [TimetableSubstitutionController::class, 'approve'])->middleware('permission:timetable.manage');
            Route::post('/substitutions/{substitution}/cancel', [TimetableSubstitutionController::class, 'cancel'])->middleware('permission:timetable.manage');

            Route::get('/exceptions', [TimetableScheduleExceptionController::class, 'index'])->middleware('permission:timetable.view');
            Route::post('/exceptions', [TimetableScheduleExceptionController::class, 'store'])->middleware('permission:timetable.manage');
            Route::get('/exceptions/{exception}', [TimetableScheduleExceptionController::class, 'show'])->middleware('permission:timetable.view');
            Route::put('/exceptions/{exception}', [TimetableScheduleExceptionController::class, 'update'])->middleware('permission:timetable.manage');
            Route::delete('/exceptions/{exception}', [TimetableScheduleExceptionController::class, 'destroy'])->middleware('permission:timetable.manage');

            Route::get('/classes/{classId}/sections/{sectionId}/weekly', [TimetableViewController::class, 'classWeekly'])->middleware('permission:timetable.view');
            Route::get('/staff/{staffId}/weekly', [TimetableViewController::class, 'staffWeekly'])->middleware('permission:timetable.view');
            Route::get('/staff/{staffId}/daily', [TimetableViewController::class, 'staffDaily'])->middleware('permission:timetable.view');

            Route::get('/publish-logs', [TimetablePublishLogController::class, 'index'])->middleware('permission:timetable.view');
        });

        Route::prefix('attendance')->group(function (): void {
            Route::get('/status-types', [AttendanceStatusTypeController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/status-types', [AttendanceStatusTypeController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/status-types/{attendanceStatusType}', [AttendanceStatusTypeController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/status-types/{attendanceStatusType}', [AttendanceStatusTypeController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/status-types/{attendanceStatusType}', [AttendanceStatusTypeController::class, 'destroy'])->middleware('permission:attendance.manage');

            Route::get('/periods', [AttendancePeriodController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/periods', [AttendancePeriodController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/periods/{attendancePeriod}', [AttendancePeriodController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/periods/{attendancePeriod}', [AttendancePeriodController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/periods/{attendancePeriod}', [AttendancePeriodController::class, 'destroy'])->middleware('permission:attendance.manage');

            Route::get('/student-sessions', [StudentAttendanceSessionController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/student-sessions', [StudentAttendanceSessionController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/student-sessions/{studentSession}', [StudentAttendanceSessionController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/student-sessions/{studentSession}', [StudentAttendanceSessionController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/student-sessions/{studentSession}', [StudentAttendanceSessionController::class, 'destroy'])->middleware('permission:attendance.manage');
            Route::post('/student-sessions/{studentSession}/bulk-mark', [StudentAttendanceSessionController::class, 'bulkMark'])->middleware('permission:attendance.manage');
            Route::post('/student-sessions/{studentSession}/submit', [StudentAttendanceSessionController::class, 'submit'])->middleware('permission:attendance.manage');
            Route::post('/student-sessions/{studentSession}/lock', [StudentAttendanceSessionController::class, 'lock'])->middleware('permission:attendance.manage');

            Route::get('/student-records', [StudentAttendanceRecordController::class, 'index'])->middleware('permission:attendance.view');
            Route::get('/student-records/{studentRecord}', [StudentAttendanceRecordController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/student-records/{studentRecord}', [StudentAttendanceRecordController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/student-records/{studentRecord}', [StudentAttendanceRecordController::class, 'destroy'])->middleware('permission:attendance.manage');
            Route::post('/students/{student}/mark', [StudentAttendanceSessionController::class, 'markForStudent'])->middleware('permission:attendance.manage');

            Route::get('/staff-records', [AttendanceStaffAttendanceController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/staff-records', [AttendanceStaffAttendanceController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/staff-records/{staffRecord}', [AttendanceStaffAttendanceController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/staff-records/{staffRecord}', [AttendanceStaffAttendanceController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/staff-records/{staffRecord}', [AttendanceStaffAttendanceController::class, 'destroy'])->middleware('permission:attendance.manage');
            Route::post('/staff/{staff}/mark', [AttendanceStaffAttendanceController::class, 'mark'])->middleware('permission:attendance.manage');

            Route::get('/corrections', [AttendanceCorrectionController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/corrections', [AttendanceCorrectionController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/corrections/{correction}', [AttendanceCorrectionController::class, 'show'])->middleware('permission:attendance.view');
            Route::post('/corrections/{correction}/approve', [AttendanceCorrectionController::class, 'approve'])->middleware('permission:attendance.manage');
            Route::post('/corrections/{correction}/reject', [AttendanceCorrectionController::class, 'reject'])->middleware('permission:attendance.manage');

            Route::get('/holidays', [AttendanceHolidayController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/holidays', [AttendanceHolidayController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/holidays/{holiday}', [AttendanceHolidayController::class, 'show'])->middleware('permission:attendance.view');
            Route::put('/holidays/{holiday}', [AttendanceHolidayController::class, 'update'])->middleware('permission:attendance.manage');
            Route::delete('/holidays/{holiday}', [AttendanceHolidayController::class, 'destroy'])->middleware('permission:attendance.manage');

            Route::get('/imports', [AttendanceImportController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/imports', [AttendanceImportController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/imports/{attendanceImport}', [AttendanceImportController::class, 'show'])->middleware('permission:attendance.view');
            Route::post('/imports/{attendanceImport}/process', [AttendanceImportController::class, 'process'])->middleware('permission:attendance.manage');

            Route::get('/biometric-logs', [BiometricLogController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/biometric-logs', [BiometricLogController::class, 'store'])->middleware('permission:attendance.manage');
            Route::get('/biometric-logs/{biometricLog}', [BiometricLogController::class, 'show'])->middleware('permission:attendance.view');
            Route::post('/biometric-logs/process', [BiometricLogController::class, 'process'])->middleware('permission:attendance.manage');

            Route::get('/summary', [AttendanceSummaryController::class, 'index'])->middleware('permission:attendance.view');
            Route::post('/summary/refresh', [AttendanceSummaryController::class, 'refresh'])->middleware('permission:attendance.manage');

            Route::prefix('/reports')->group(function (): void {
                Route::get('/student-summary', [AttendanceReportController::class, 'studentSummary'])->middleware('permission:attendance.view');
                Route::get('/staff-summary', [AttendanceReportController::class, 'staffSummary'])->middleware('permission:attendance.view');
                Route::get('/class-attendance', [AttendanceReportController::class, 'classAttendance'])->middleware('permission:attendance.view');
                Route::get('/defaulters', [AttendanceReportController::class, 'defaulters'])->middleware('permission:attendance.view');
            });
        });

        Route::prefix('communication')->group(function (): void {
            Route::get('/channels', [CommunicationChannelController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/channels', [CommunicationChannelController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/channels/{channel}', [CommunicationChannelController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/channels/{channel}', [CommunicationChannelController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/channels/{channel}', [CommunicationChannelController::class, 'destroy'])->middleware('permission:communication.manage');

            Route::get('/templates', [MessageTemplateController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/templates', [MessageTemplateController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/templates/{template}', [MessageTemplateController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/templates/{template}', [MessageTemplateController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/templates/{template}', [MessageTemplateController::class, 'destroy'])->middleware('permission:communication.manage');

            Route::get('/announcements', [CommunicationAnnouncementController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/announcements', [CommunicationAnnouncementController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/announcements/{announcement}', [CommunicationAnnouncementController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/announcements/{announcement}', [CommunicationAnnouncementController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/announcements/{announcement}', [CommunicationAnnouncementController::class, 'destroy'])->middleware('permission:communication.manage');
            Route::post('/announcements/{announcement}/publish', [CommunicationAnnouncementController::class, 'publish'])->middleware('permission:communication.manage');
            Route::post('/announcements/{announcement}/cancel', [CommunicationAnnouncementController::class, 'cancel'])->middleware('permission:communication.manage');
            Route::get('/announcements/{announcement}/recipients', [CommunicationAnnouncementController::class, 'recipients'])->middleware('permission:communication.view');

            Route::get('/messages', [CommunicationMessageController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/messages', [CommunicationMessageController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/messages/{message}', [CommunicationMessageController::class, 'show'])->middleware('permission:communication.view');
            Route::delete('/messages/{message}', [CommunicationMessageController::class, 'destroy'])->middleware('permission:communication.manage');
            Route::post('/messages/{message}/mark-read', [CommunicationMessageController::class, 'markRead'])->middleware('permission:communication.view');
            Route::post('/messages/{message}/archive', [CommunicationMessageController::class, 'archive'])->middleware('permission:communication.manage');

            Route::get('/conversations', [ConversationController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/conversations', [ConversationController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/conversations/{conversation}', [ConversationController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/conversations/{conversation}', [ConversationController::class, 'destroy'])->middleware('permission:communication.manage');
            Route::get('/conversations/{conversation}/messages', [ConversationController::class, 'messages'])->middleware('permission:communication.view');
            Route::post('/conversations/{conversation}/participants', [ConversationController::class, 'addParticipant'])->middleware('permission:communication.manage');
            Route::delete('/conversations/{conversation}/participants/{participantId}', [ConversationController::class, 'removeParticipant'])->middleware('permission:communication.manage');

            Route::get('/notifications', [CommunicationNotificationController::class, 'index'])->middleware('permission:communication.view');
            Route::get('/notifications/{notification}', [CommunicationNotificationController::class, 'show'])->middleware('permission:communication.view');
            Route::post('/notifications/{notification}/mark-read', [CommunicationNotificationController::class, 'markRead'])->middleware('permission:communication.view');

            Route::get('/scheduled-messages', [ScheduledMessageController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/scheduled-messages', [ScheduledMessageController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/scheduled-messages/{scheduledMessage}', [ScheduledMessageController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/scheduled-messages/{scheduledMessage}', [ScheduledMessageController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/scheduled-messages/{scheduledMessage}', [ScheduledMessageController::class, 'destroy'])->middleware('permission:communication.manage');
            Route::post('/scheduled-messages/{scheduledMessage}/cancel', [ScheduledMessageController::class, 'cancel'])->middleware('permission:communication.manage');
            Route::post('/scheduled-messages/process-due', [ScheduledMessageController::class, 'processDue'])->middleware('permission:communication.manage');

            Route::get('/groups', [CommunicationGroupController::class, 'index'])->middleware('permission:communication.view');
            Route::post('/groups', [CommunicationGroupController::class, 'store'])->middleware('permission:communication.manage');
            Route::get('/groups/{group}', [CommunicationGroupController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/groups/{group}', [CommunicationGroupController::class, 'update'])->middleware('permission:communication.manage');
            Route::delete('/groups/{group}', [CommunicationGroupController::class, 'destroy'])->middleware('permission:communication.manage');
            Route::post('/groups/{group}/members', [CommunicationGroupController::class, 'addMember'])->middleware('permission:communication.manage');
            Route::delete('/groups/{group}/members/{memberId}', [CommunicationGroupController::class, 'removeMember'])->middleware('permission:communication.manage');

            Route::get('/preferences', [NotificationPreferenceController::class, 'index'])->middleware('permission:communication.view');
            Route::get('/preferences/{preference}', [NotificationPreferenceController::class, 'show'])->middleware('permission:communication.view');
            Route::put('/preferences/{preference}', [NotificationPreferenceController::class, 'update'])->middleware('permission:communication.manage');

            Route::prefix('reports')->group(function (): void {
                Route::get('/notification-delivery', [CommunicationNotificationController::class, 'notificationDeliveryReport'])->middleware('permission:communication.view');
                Route::get('/announcement-engagement', [CommunicationNotificationController::class, 'announcementEngagementReport'])->middleware('permission:communication.view');
                Route::get('/message-volume', [CommunicationNotificationController::class, 'messageVolumeReport'])->middleware('permission:communication.view');
            });
        });

        Route::prefix('exams')->group(function (): void {
            Route::get('/types', [ExamTypeController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/types', [ExamTypeController::class, 'store'])->middleware('permission:exams.manage');
            Route::get('/types/{examType}', [ExamTypeController::class, 'show'])->middleware('permission:exams.view');
            Route::put('/types/{examType}', [ExamTypeController::class, 'update'])->middleware('permission:exams.manage');
            Route::delete('/types/{examType}', [ExamTypeController::class, 'destroy'])->middleware('permission:exams.manage');

            Route::get('/', [ExaminationExamController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/', [ExaminationExamController::class, 'store'])->middleware('permission:exams.manage');

            Route::get('/subjects/list', [ExamSubjectController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/subjects', [ExamSubjectController::class, 'store'])->middleware('permission:exams.manage');
            Route::get('/subjects/{examSubject}', [ExamSubjectController::class, 'show'])->middleware('permission:exams.view');
            Route::delete('/subjects/{examSubject}', [ExamSubjectController::class, 'destroy'])->middleware('permission:exams.manage');

            Route::get('/marks', [ExamMarkController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/marks', [ExamMarkController::class, 'store'])->middleware('permission:exams.manage');
            Route::post('/marks/bulk', [ExamMarkController::class, 'bulkStore'])->middleware('permission:exams.manage');
            Route::get('/marks/{examMark}', [ExamMarkController::class, 'show'])->middleware('permission:exams.view');
            Route::delete('/marks/{examMark}', [ExamMarkController::class, 'destroy'])->middleware('permission:exams.manage');

            Route::get('/grading-systems', [GradingSystemController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/grading-systems', [GradingSystemController::class, 'store'])->middleware('permission:exams.manage');
            Route::get('/grading-systems/{gradingSystem}', [GradingSystemController::class, 'show'])->middleware('permission:exams.view');
            Route::put('/grading-systems/{gradingSystem}', [GradingSystemController::class, 'update'])->middleware('permission:exams.manage');
            Route::delete('/grading-systems/{gradingSystem}', [GradingSystemController::class, 'destroy'])->middleware('permission:exams.manage');
            Route::post('/grading-systems/{gradingSystem}/scales', [GradingSystemController::class, 'addScale'])->middleware('permission:exams.manage');
            Route::delete('/grading-scales/{gradeScale}', [GradingSystemController::class, 'removeScale'])->middleware('permission:exams.manage');

            Route::get('/results', [ResultController::class, 'index'])->middleware('permission:exams.view');
            Route::get('/results/records/{studentResult}', [ResultController::class, 'show'])->middleware('permission:exams.view');
            Route::post('/results/{examId}/compute', [ResultController::class, 'compute'])->middleware('permission:exams.manage');
            Route::get('/results/{examId}/student/{studentId}', [ResultController::class, 'studentResult'])->middleware('permission:exams.view');
            Route::get('/results/{examId}/class/{classId}', [ResultController::class, 'classResults'])->middleware('permission:exams.view');
            Route::get('/results/{examId}/merit-list', [ResultController::class, 'meritList'])->middleware('permission:exams.view');

            Route::get('/publications', [ResultPublicationController::class, 'index'])->middleware('permission:exams.view');
            Route::get('/publications/{resultPublication}', [ResultPublicationController::class, 'show'])->middleware('permission:exams.view');
            Route::post('/results/{examId}/publish', [ResultPublicationController::class, 'publish'])->middleware('permission:exams.manage');

            Route::get('/revaluation', [RevaluationController::class, 'index'])->middleware('permission:exams.view');
            Route::post('/revaluation', [RevaluationController::class, 'store'])->middleware('permission:exams.manage');
            Route::get('/revaluation/{revaluation}', [RevaluationController::class, 'show'])->middleware('permission:exams.view');
            Route::delete('/revaluation/{revaluation}', [RevaluationController::class, 'destroy'])->middleware('permission:exams.manage');

            Route::get('/{exam}', [ExaminationExamController::class, 'show'])->middleware('permission:exams.view');
            Route::put('/{exam}', [ExaminationExamController::class, 'update'])->middleware('permission:exams.manage');
            Route::delete('/{exam}', [ExaminationExamController::class, 'destroy'])->middleware('permission:exams.manage');
            Route::post('/{exam}/enroll-students', [ExaminationExamController::class, 'enrollStudents'])->middleware('permission:exams.manage');
        });

        Route::prefix('transport')->group(function (): void {
            Route::get('/vehicles', [VehicleController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/vehicles', [VehicleController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->middleware('permission:transport.manage');
            Route::get('/vehicles/{vehicle}/location', [VehicleController::class, 'location'])->middleware('permission:transport.view');

            Route::get('/drivers', [DriverController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/drivers', [DriverController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/drivers/{driver}', [DriverController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/drivers/{driver}', [DriverController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->middleware('permission:transport.manage');

            Route::get('/routes', [RouteController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/routes', [RouteController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/routes/{route}', [RouteController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/routes/{route}', [RouteController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/routes/{route}', [RouteController::class, 'destroy'])->middleware('permission:transport.manage');

            Route::get('/route-stops', [RouteStopController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/route-stops', [RouteStopController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/route-stops/{routeStop}', [RouteStopController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/route-stops/{routeStop}', [RouteStopController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/route-stops/{routeStop}', [RouteStopController::class, 'destroy'])->middleware('permission:transport.manage');
            Route::get('/routes/{route}/stops', [RouteStopController::class, 'routeStops'])->middleware('permission:transport.view');

            Route::get('/route-vehicle-assignments', [RouteController::class, 'assignmentIndex'])->middleware('permission:transport.view');
            Route::post('/route-vehicle-assignments', [RouteController::class, 'assignmentStore'])->middleware('permission:transport.manage');
            Route::get('/route-vehicle-assignments/{assignment}', [RouteController::class, 'assignmentShow'])->middleware('permission:transport.view');
            Route::put('/route-vehicle-assignments/{assignment}', [RouteController::class, 'assignmentUpdate'])->middleware('permission:transport.manage');
            Route::delete('/route-vehicle-assignments/{assignment}', [RouteController::class, 'assignmentDestroy'])->middleware('permission:transport.manage');

            Route::get('/student-allocations', [AllocationController::class, 'studentIndex'])->middleware('permission:transport.view');
            Route::post('/student-allocations', [AllocationController::class, 'studentStore'])->middleware('permission:transport.manage');
            Route::get('/student-allocations/{allocation}', [AllocationController::class, 'studentShow'])->middleware('permission:transport.view');
            Route::put('/student-allocations/{allocation}', [AllocationController::class, 'studentUpdate'])->middleware('permission:transport.manage');
            Route::delete('/student-allocations/{allocation}', [AllocationController::class, 'studentDestroy'])->middleware('permission:transport.manage');

            Route::get('/staff-allocations', [AllocationController::class, 'staffIndex'])->middleware('permission:transport.view');
            Route::post('/staff-allocations', [AllocationController::class, 'staffStore'])->middleware('permission:transport.manage');
            Route::get('/staff-allocations/{allocation}', [AllocationController::class, 'staffShow'])->middleware('permission:transport.view');
            Route::put('/staff-allocations/{allocation}', [AllocationController::class, 'staffUpdate'])->middleware('permission:transport.manage');
            Route::delete('/staff-allocations/{allocation}', [AllocationController::class, 'staffDestroy'])->middleware('permission:transport.manage');

            Route::get('/trips', [TripController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/trips', [TripController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/trips/{trip}', [TripController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/trips/{trip}', [TripController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/trips/{trip}', [TripController::class, 'destroy'])->middleware('permission:transport.manage');
            Route::post('/trips/{trip}/start', [TripController::class, 'start'])->middleware('permission:transport.manage');
            Route::post('/trips/{trip}/complete', [TripController::class, 'complete'])->middleware('permission:transport.manage');
            Route::post('/trips/{trip}/cancel', [TripController::class, 'cancel'])->middleware('permission:transport.manage');
            Route::post('/trips/{trip}/mark-boarded', [TripController::class, 'markBoarded'])->middleware('permission:transport.manage');
            Route::post('/trips/{trip}/mark-dropped', [TripController::class, 'markDropped'])->middleware('permission:transport.manage');
            Route::get('/trip-logs', [TripController::class, 'logs'])->middleware('permission:transport.view');

            Route::get('/maintenance', [MaintenanceController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/maintenance', [MaintenanceController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])->middleware('permission:transport.manage');

            Route::get('/fuel-logs', [FuelController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/fuel-logs', [FuelController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/fuel-logs/{fuel}', [FuelController::class, 'show'])->middleware('permission:transport.view');
            Route::put('/fuel-logs/{fuel}', [FuelController::class, 'update'])->middleware('permission:transport.manage');
            Route::delete('/fuel-logs/{fuel}', [FuelController::class, 'destroy'])->middleware('permission:transport.manage');

            Route::get('/gps-logs', [GpsController::class, 'index'])->middleware('permission:transport.view');
            Route::post('/gps-logs', [GpsController::class, 'store'])->middleware('permission:transport.manage');
            Route::get('/gps-logs/{gps}', [GpsController::class, 'show'])->middleware('permission:transport.view');
            Route::get('/vehicle-location/{vehicle}', [GpsController::class, 'vehicleLocation'])->middleware('permission:transport.view');

            Route::get('/reports', [TripController::class, 'reports'])->middleware('permission:transport.view');
        });

        Route::prefix('reports')->group(function (): void {
            Route::get('/dashboard', [ReportsDashboardController::class, 'overview'])->middleware('permission:reports.view');
            Route::get('/widgets', [ReportsDashboardController::class, 'widgets'])->middleware('permission:reports.view');
            Route::put('/dashboard/layout', [ReportsDashboardController::class, 'updateLayout'])->middleware('permission:reports.manage');

            Route::get('/definitions', [ReportDefinitionController::class, 'index'])->middleware('permission:reports.view');
            Route::post('/definitions', [ReportDefinitionController::class, 'store'])->middleware('permission:reports.manage');
            Route::get('/definitions/{reportDefinition}', [ReportDefinitionController::class, 'show'])->middleware('permission:reports.view');
            Route::put('/definitions/{reportDefinition}', [ReportDefinitionController::class, 'update'])->middleware('permission:reports.manage');
            Route::delete('/definitions/{reportDefinition}', [ReportDefinitionController::class, 'destroy'])->middleware('permission:reports.manage');

            Route::post('/run', [ReportRunController::class, 'run'])->middleware('permission:reports.run');
            Route::get('/runs', [ReportRunController::class, 'index'])->middleware('permission:reports.view');
            Route::get('/runs/{reportRun}', [ReportRunController::class, 'show'])->middleware('permission:reports.view');

            Route::get('/schedules', [ReportScheduleController::class, 'index'])->middleware('permission:reports.view');
            Route::post('/schedules', [ReportScheduleController::class, 'store'])->middleware('permission:reports.manage');
            Route::get('/schedules/{reportSchedule}', [ReportScheduleController::class, 'show'])->middleware('permission:reports.view');
            Route::put('/schedules/{reportSchedule}', [ReportScheduleController::class, 'update'])->middleware('permission:reports.manage');
            Route::post('/schedules/{reportSchedule}/pause', [ReportScheduleController::class, 'pause'])->middleware('permission:reports.manage');
            Route::post('/schedules/{reportSchedule}/resume', [ReportScheduleController::class, 'resume'])->middleware('permission:reports.manage');

            Route::get('/exports/{reportExport}/download', [ReportExportController::class, 'download'])->middleware('permission:reports.export');
        });

        Route::prefix('portal')->group(function (): void {
            Route::get('/context', [PortalContextController::class, 'context'])->middleware('permission:portal.view');
            Route::post('/context/switch', [PortalContextController::class, 'switch'])->middleware('permission:portal.view');
            Route::get('/profiles', [PortalContextController::class, 'profiles'])->middleware('permission:portal.view');
            Route::get('/accessible-students', [PortalContextController::class, 'accessibleStudents'])->middleware('permission:portal.view');

            Route::get('/dashboard', [PortalDashboardController::class, 'show'])->middleware('permission:portal.view');

            Route::get('/students/{studentId}/overview', [PortalStudentController::class, 'overview'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/attendance', [PortalStudentController::class, 'attendance'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/fees', [PortalStudentController::class, 'fees'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/results', [PortalStudentController::class, 'results'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/timetable', [PortalStudentController::class, 'timetable'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/assignments', [PortalStudentController::class, 'assignments'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/transport', [PortalStudentController::class, 'transport'])->middleware('permission:portal.view');
            Route::get('/students/{studentId}/documents', [PortalStudentController::class, 'documents'])->middleware('permission:portal.view');

            Route::get('/notifications', [PortalNotificationController::class, 'index'])->middleware('permission:portal.view');
            Route::post('/notifications/{notification}/read', [PortalNotificationController::class, 'markRead'])->middleware('permission:portal.view');
            Route::post('/notifications/read-all', [PortalNotificationController::class, 'markAllRead'])->middleware('permission:portal.view');

            Route::post('/profiles/link-student', [PortalProfileController::class, 'linkStudent'])->middleware('permission:portal.manage');
            Route::post('/profiles/link-guardian', [PortalProfileController::class, 'linkGuardian'])->middleware('permission:portal.manage');
            Route::put('/profiles/access/{id}', [PortalProfileController::class, 'updateAccess'])->middleware('permission:portal.manage');
        });
    });
});
