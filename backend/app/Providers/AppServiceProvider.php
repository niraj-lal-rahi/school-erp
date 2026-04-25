<?php

namespace App\Providers;

use App\Models\AcademicManagement\AcademicCalendarEvent;
use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicManagement\ClassSubjectAssignment;
use App\Models\AcademicManagement\Curriculum;
use App\Models\AcademicManagement\GradingStructure;
use App\Models\AcademicManagement\HomeworkAssignment;
use App\Models\AcademicManagement\LessonPlan;
use App\Models\AcademicManagement\Subject;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Attendance\AttendancePeriod;
use App\Models\Attendance\AttendanceCorrection;
use App\Models\Attendance\AttendanceHoliday;
use App\Models\Attendance\AttendanceImport;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\AttendanceStatusType;
use App\Models\Attendance\BiometricLog;
use App\Models\Attendance\StudentAttendanceRecord;
use App\Models\Attendance\StudentAttendanceSession;
use App\Models\Finance\FeeCategory;
use App\Models\Finance\Expense;
use App\Models\Finance\ExpenseCategory;
use App\Models\Finance\DiscountType;
use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeInstallment;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\FineRule;
use App\Models\Finance\LedgerAccount;
use App\Models\Finance\LedgerEntry;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use App\Models\Finance\Refund;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentDiscount;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\HR\Department;
use App\Models\HR\Designation;
use App\Models\HR\Staff;
use App\Models\Admission;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentCategory;
use App\Models\StudentDocument;
use App\Models\StudentHouse;
use App\Models\StudentMedicalRecord;
use App\Models\StudentNote;
use App\Models\StudentStatusHistory;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Policies\AcademicManagement\AcademicManagementPolicy;
use App\Policies\Attendance\AttendancePeriodPolicy;
use App\Policies\Attendance\AttendanceCorrectionPolicy;
use App\Policies\Attendance\AttendanceHolidayPolicy;
use App\Policies\Attendance\AttendanceImportPolicy;
use App\Policies\Attendance\AttendanceSummaryPolicy;
use App\Policies\Attendance\AttendanceStatusTypePolicy;
use App\Policies\Attendance\BiometricLogPolicy;
use App\Policies\Attendance\StudentAttendanceRecordPolicy;
use App\Policies\Attendance\StudentAttendanceSessionPolicy;
use App\Policies\Finance\FeeCategoryPolicy;
use App\Policies\Finance\ExpenseCategoryPolicy;
use App\Policies\Finance\ExpensePolicy;
use App\Policies\Finance\DiscountTypePolicy;
use App\Policies\Finance\FeeHeadPolicy;
use App\Policies\Finance\FeeInstallmentPolicy;
use App\Policies\Finance\FeeInvoicePolicy;
use App\Policies\Finance\FineRulePolicy;
use App\Policies\Finance\LedgerAccountPolicy;
use App\Policies\Finance\LedgerEntryPolicy;
use App\Policies\Finance\PaymentPolicy;
use App\Policies\Finance\ReceiptPolicy;
use App\Policies\Finance\RefundPolicy;
use App\Policies\Finance\FeeStructurePolicy;
use App\Policies\Finance\StudentDiscountPolicy;
use App\Policies\Finance\StudentFeeAssignmentPolicy;
use App\Policies\HR\DepartmentPolicy;
use App\Policies\HR\DesignationPolicy;
use App\Policies\HR\StaffPolicy;
use App\Policies\GuardianPolicy;
use App\Policies\StudentCategoryPolicy;
use App\Policies\StudentDocumentPolicy;
use App\Policies\StudentHousePolicy;
use App\Policies\StudentMedicalRecordPolicy;
use App\Policies\StudentNotePolicy;
use App\Policies\StudentPolicy;
use App\Repositories\Contracts\AcademicManagement\AcademicCalendarEventRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\AcademicTermRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\AcademicYearRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\ClassSubjectAssignmentRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\CurriculumRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\GradingStructureRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\HomeworkAssignmentRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\LessonPlanRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\SchoolClassRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\SectionRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\SubjectRepositoryInterface;
use App\Repositories\Contracts\AcademicManagement\TeacherAssignmentRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendancePeriodRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendanceCorrectionRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendanceHolidayRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendanceImportRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendanceSummaryRepositoryInterface;
use App\Repositories\Contracts\Attendance\AttendanceStatusTypeRepositoryInterface;
use App\Repositories\Contracts\Attendance\BiometricLogRepositoryInterface;
use App\Repositories\Contracts\Attendance\StudentAttendanceRecordRepositoryInterface;
use App\Repositories\Contracts\Attendance\StudentAttendanceSessionRepositoryInterface;
use App\Repositories\Contracts\Finance\FeeCategoryRepositoryInterface;
use App\Repositories\Contracts\Finance\ExpenseCategoryRepositoryInterface;
use App\Repositories\Contracts\Finance\ExpenseRepositoryInterface;
use App\Repositories\Contracts\Finance\DiscountTypeRepositoryInterface;
use App\Repositories\Contracts\Finance\FeeHeadRepositoryInterface;
use App\Repositories\Contracts\Finance\FeeInstallmentRepositoryInterface;
use App\Repositories\Contracts\Finance\FeeInvoiceRepositoryInterface;
use App\Repositories\Contracts\Finance\FineRuleRepositoryInterface;
use App\Repositories\Contracts\Finance\LedgerAccountRepositoryInterface;
use App\Repositories\Contracts\Finance\LedgerEntryRepositoryInterface;
use App\Repositories\Contracts\Finance\PaymentRepositoryInterface;
use App\Repositories\Contracts\Finance\ReceiptRepositoryInterface;
use App\Repositories\Contracts\Finance\RefundRepositoryInterface;
use App\Repositories\Contracts\Finance\FeeStructureRepositoryInterface;
use App\Repositories\Contracts\Finance\StudentDiscountRepositoryInterface;
use App\Repositories\Contracts\Finance\StudentFeeAssignmentRepositoryInterface;
use App\Repositories\Contracts\HR\DepartmentRepositoryInterface;
use App\Repositories\Contracts\HR\DesignationRepositoryInterface;
use App\Repositories\Contracts\HR\StaffRepositoryInterface;
use App\Repositories\Contracts\HR\StaffDocumentRepositoryInterface;
use App\Repositories\Contracts\HR\StaffEmergencyContactRepositoryInterface;
use App\Repositories\Contracts\HR\StaffAttendanceRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveBalanceRepositoryInterface;
use App\Repositories\Contracts\HR\LeaveTypeRepositoryInterface;
use App\Repositories\Contracts\HR\PayrollRunRepositoryInterface;
use App\Repositories\Contracts\HR\SalaryComponentRepositoryInterface;
use App\Repositories\Contracts\HR\SalaryStructureRepositoryInterface;
use App\Repositories\Contracts\HR\StaffBankDetailRepositoryInterface;
use App\Repositories\Contracts\HR\StaffLeaveApplicationRepositoryInterface;
use App\Repositories\Contracts\HR\StaffNoteRepositoryInterface;
use App\Repositories\Contracts\HR\StaffPayslipRepositoryInterface;
use App\Repositories\Contracts\HR\StaffQualificationRepositoryInterface;
use App\Repositories\Contracts\HR\StaffStatusHistoryRepositoryInterface;
use App\Repositories\Contracts\HR\StaffWorkExperienceRepositoryInterface;
use App\Repositories\Eloquent\AcademicManagement\AcademicCalendarEventRepository;
use App\Repositories\Eloquent\AcademicManagement\AcademicTermRepository;
use App\Repositories\Eloquent\AcademicManagement\AcademicYearRepository;
use App\Repositories\Eloquent\AcademicManagement\ClassSubjectAssignmentRepository;
use App\Repositories\Eloquent\AcademicManagement\CurriculumRepository;
use App\Repositories\Eloquent\AcademicManagement\GradingStructureRepository;
use App\Repositories\Eloquent\AcademicManagement\HomeworkAssignmentRepository;
use App\Repositories\Eloquent\AcademicManagement\LessonPlanRepository;
use App\Repositories\Eloquent\AcademicManagement\SchoolClassRepository;
use App\Repositories\Eloquent\AcademicManagement\SectionRepository;
use App\Repositories\Eloquent\AcademicManagement\SubjectRepository;
use App\Repositories\Eloquent\AcademicManagement\TeacherAssignmentRepository;
use App\Repositories\Eloquent\Attendance\AttendancePeriodRepository;
use App\Repositories\Eloquent\Attendance\AttendanceCorrectionRepository;
use App\Repositories\Eloquent\Attendance\AttendanceHolidayRepository;
use App\Repositories\Eloquent\Attendance\AttendanceImportRepository;
use App\Repositories\Eloquent\Attendance\AttendanceSummaryRepository;
use App\Repositories\Eloquent\Attendance\AttendanceStatusTypeRepository;
use App\Repositories\Eloquent\Attendance\BiometricLogRepository;
use App\Repositories\Eloquent\Attendance\StudentAttendanceRecordRepository;
use App\Repositories\Eloquent\Attendance\StudentAttendanceSessionRepository;
use App\Repositories\Eloquent\Finance\FeeCategoryRepository;
use App\Repositories\Eloquent\Finance\ExpenseCategoryRepository;
use App\Repositories\Eloquent\Finance\ExpenseRepository;
use App\Repositories\Eloquent\Finance\DiscountTypeRepository;
use App\Repositories\Eloquent\Finance\FeeHeadRepository;
use App\Repositories\Eloquent\Finance\FeeInstallmentRepository;
use App\Repositories\Eloquent\Finance\FeeInvoiceRepository;
use App\Repositories\Eloquent\Finance\FineRuleRepository;
use App\Repositories\Eloquent\Finance\LedgerAccountRepository;
use App\Repositories\Eloquent\Finance\LedgerEntryRepository;
use App\Repositories\Eloquent\Finance\PaymentRepository;
use App\Repositories\Eloquent\Finance\ReceiptRepository;
use App\Repositories\Eloquent\Finance\RefundRepository;
use App\Repositories\Eloquent\Finance\FeeStructureRepository;
use App\Repositories\Eloquent\Finance\StudentDiscountRepository;
use App\Repositories\Eloquent\Finance\StudentFeeAssignmentRepository;
use App\Repositories\Eloquent\HR\DepartmentRepository;
use App\Repositories\Eloquent\HR\DesignationRepository;
use App\Repositories\Eloquent\HR\StaffRepository;
use App\Repositories\Eloquent\HR\StaffDocumentRepository;
use App\Repositories\Eloquent\HR\StaffEmergencyContactRepository;
use App\Repositories\Eloquent\HR\StaffAttendanceRepository;
use App\Repositories\Eloquent\HR\LeaveBalanceRepository;
use App\Repositories\Eloquent\HR\LeaveTypeRepository;
use App\Repositories\Eloquent\HR\PayrollRunRepository;
use App\Repositories\Eloquent\HR\SalaryComponentRepository;
use App\Repositories\Eloquent\HR\SalaryStructureRepository;
use App\Repositories\Eloquent\HR\StaffBankDetailRepository;
use App\Repositories\Eloquent\HR\StaffLeaveApplicationRepository;
use App\Repositories\Eloquent\HR\StaffNoteRepository;
use App\Repositories\Eloquent\HR\StaffPayslipRepository;
use App\Repositories\Eloquent\HR\StaffQualificationRepository;
use App\Repositories\Eloquent\HR\StaffStatusHistoryRepository;
use App\Repositories\Eloquent\HR\StaffWorkExperienceRepository;
use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Repositories\Contracts\AdmissionRepositoryInterface;
use App\Repositories\Contracts\GuardianRepositoryInterface;
use App\Repositories\Contracts\StudentCategoryRepositoryInterface;
use App\Repositories\Contracts\StudentDocumentRepositoryInterface;
use App\Repositories\Contracts\StudentEnrollmentRepositoryInterface;
use App\Repositories\Contracts\StudentHouseRepositoryInterface;
use App\Repositories\Contracts\StudentMedicalRecordRepositoryInterface;
use App\Repositories\Contracts\StudentNoteRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\StudentStatusHistoryRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AdmissionRepository;
use App\Repositories\Eloquent\GuardianRepository;
use App\Repositories\Eloquent\SchoolRepository;
use App\Repositories\Eloquent\StudentCategoryRepository;
use App\Repositories\Eloquent\StudentDocumentRepository;
use App\Repositories\Eloquent\StudentEnrollmentRepository;
use App\Repositories\Eloquent\StudentHouseRepository;
use App\Repositories\Eloquent\StudentMedicalRecordRepository;
use App\Repositories\Eloquent\StudentNoteRepository;
use App\Repositories\Eloquent\StudentRepository;
use App\Repositories\Eloquent\StudentStatusHistoryRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Support\Auth\JwtManager;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
        $this->app->singleton(JwtManager::class);

        $this->app->bind(SchoolRepositoryInterface::class, SchoolRepository::class);
        $this->app->bind(AttendanceStatusTypeRepositoryInterface::class, AttendanceStatusTypeRepository::class);
        $this->app->bind(AttendancePeriodRepositoryInterface::class, AttendancePeriodRepository::class);
        $this->app->bind(AttendanceCorrectionRepositoryInterface::class, AttendanceCorrectionRepository::class);
        $this->app->bind(AttendanceHolidayRepositoryInterface::class, AttendanceHolidayRepository::class);
        $this->app->bind(AttendanceImportRepositoryInterface::class, AttendanceImportRepository::class);
        $this->app->bind(AttendanceSummaryRepositoryInterface::class, AttendanceSummaryRepository::class);
        $this->app->bind(BiometricLogRepositoryInterface::class, BiometricLogRepository::class);
        $this->app->bind(StudentAttendanceSessionRepositoryInterface::class, StudentAttendanceSessionRepository::class);
        $this->app->bind(StudentAttendanceRecordRepositoryInterface::class, StudentAttendanceRecordRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
        $this->app->bind(FeeCategoryRepositoryInterface::class, FeeCategoryRepository::class);
        $this->app->bind(ExpenseCategoryRepositoryInterface::class, ExpenseCategoryRepository::class);
        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
        $this->app->bind(DiscountTypeRepositoryInterface::class, DiscountTypeRepository::class);
        $this->app->bind(FeeHeadRepositoryInterface::class, FeeHeadRepository::class);
        $this->app->bind(FeeInstallmentRepositoryInterface::class, FeeInstallmentRepository::class);
        $this->app->bind(FeeInvoiceRepositoryInterface::class, FeeInvoiceRepository::class);
        $this->app->bind(FineRuleRepositoryInterface::class, FineRuleRepository::class);
        $this->app->bind(LedgerAccountRepositoryInterface::class, LedgerAccountRepository::class);
        $this->app->bind(LedgerEntryRepositoryInterface::class, LedgerEntryRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(ReceiptRepositoryInterface::class, ReceiptRepository::class);
        $this->app->bind(RefundRepositoryInterface::class, RefundRepository::class);
        $this->app->bind(FeeStructureRepositoryInterface::class, FeeStructureRepository::class);
        $this->app->bind(StudentDiscountRepositoryInterface::class, StudentDiscountRepository::class);
        $this->app->bind(StudentFeeAssignmentRepositoryInterface::class, StudentFeeAssignmentRepository::class);
        $this->app->bind(DesignationRepositoryInterface::class, DesignationRepository::class);
        $this->app->bind(StaffRepositoryInterface::class, StaffRepository::class);
        $this->app->bind(StaffDocumentRepositoryInterface::class, StaffDocumentRepository::class);
        $this->app->bind(StaffEmergencyContactRepositoryInterface::class, StaffEmergencyContactRepository::class);
        $this->app->bind(StaffAttendanceRepositoryInterface::class, StaffAttendanceRepository::class);
        $this->app->bind(LeaveTypeRepositoryInterface::class, LeaveTypeRepository::class);
        $this->app->bind(StaffLeaveApplicationRepositoryInterface::class, StaffLeaveApplicationRepository::class);
        $this->app->bind(LeaveBalanceRepositoryInterface::class, LeaveBalanceRepository::class);
        $this->app->bind(SalaryComponentRepositoryInterface::class, SalaryComponentRepository::class);
        $this->app->bind(SalaryStructureRepositoryInterface::class, SalaryStructureRepository::class);
        $this->app->bind(PayrollRunRepositoryInterface::class, PayrollRunRepository::class);
        $this->app->bind(StaffBankDetailRepositoryInterface::class, StaffBankDetailRepository::class);
        $this->app->bind(StaffStatusHistoryRepositoryInterface::class, StaffStatusHistoryRepository::class);
        $this->app->bind(StaffNoteRepositoryInterface::class, StaffNoteRepository::class);
        $this->app->bind(StaffPayslipRepositoryInterface::class, StaffPayslipRepository::class);
        $this->app->bind(StaffQualificationRepositoryInterface::class, StaffQualificationRepository::class);
        $this->app->bind(StaffWorkExperienceRepositoryInterface::class, StaffWorkExperienceRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AdmissionRepositoryInterface::class, AdmissionRepository::class);
        $this->app->bind(GuardianRepositoryInterface::class, GuardianRepository::class);
        $this->app->bind(StudentCategoryRepositoryInterface::class, StudentCategoryRepository::class);
        $this->app->bind(StudentDocumentRepositoryInterface::class, StudentDocumentRepository::class);
        $this->app->bind(StudentEnrollmentRepositoryInterface::class, StudentEnrollmentRepository::class);
        $this->app->bind(StudentHouseRepositoryInterface::class, StudentHouseRepository::class);
        $this->app->bind(StudentMedicalRecordRepositoryInterface::class, StudentMedicalRecordRepository::class);
        $this->app->bind(StudentNoteRepositoryInterface::class, StudentNoteRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(StudentStatusHistoryRepositoryInterface::class, StudentStatusHistoryRepository::class);
        $this->app->bind(AcademicYearRepositoryInterface::class, AcademicYearRepository::class);
        $this->app->bind(AcademicTermRepositoryInterface::class, AcademicTermRepository::class);
        $this->app->bind(SchoolClassRepositoryInterface::class, SchoolClassRepository::class);
        $this->app->bind(SectionRepositoryInterface::class, SectionRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(ClassSubjectAssignmentRepositoryInterface::class, ClassSubjectAssignmentRepository::class);
        $this->app->bind(TeacherAssignmentRepositoryInterface::class, TeacherAssignmentRepository::class);
        $this->app->bind(CurriculumRepositoryInterface::class, CurriculumRepository::class);
        $this->app->bind(LessonPlanRepositoryInterface::class, LessonPlanRepository::class);
        $this->app->bind(HomeworkAssignmentRepositoryInterface::class, HomeworkAssignmentRepository::class);
        $this->app->bind(AcademicCalendarEventRepositoryInterface::class, AcademicCalendarEventRepository::class);
        $this->app->bind(GradingStructureRepositoryInterface::class, GradingStructureRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::viaRequest('jwt', function ($request): ?User {
            $token = $request->bearerToken();

            if (! $token) {
                return null;
            }

            $payload = app(JwtManager::class)->decode($token);

            /** @var User|null $user */
            $user = User::query()
                ->withoutGlobalScopes()
                ->find($payload['sub'] ?? null);

            if (! $user) {
                return null;
            }

            if (($payload['school_id'] ?? null) && $user->school_id !== $payload['school_id']) {
                return null;
            }

            app(TenantContext::class)->set($user->school);

            return $user;
        });

        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(AttendanceStatusType::class, AttendanceStatusTypePolicy::class);
        Gate::policy(AttendancePeriod::class, AttendancePeriodPolicy::class);
        Gate::policy(AttendanceHoliday::class, AttendanceHolidayPolicy::class);
        Gate::policy(AttendanceCorrection::class, AttendanceCorrectionPolicy::class);
        Gate::policy(AttendanceImport::class, AttendanceImportPolicy::class);
        Gate::policy(AttendanceSummary::class, AttendanceSummaryPolicy::class);
        Gate::policy(BiometricLog::class, BiometricLogPolicy::class);
        Gate::policy(StudentAttendanceSession::class, StudentAttendanceSessionPolicy::class);
        Gate::policy(StudentAttendanceRecord::class, StudentAttendanceRecordPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(FeeCategory::class, FeeCategoryPolicy::class);
        Gate::policy(ExpenseCategory::class, ExpenseCategoryPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(DiscountType::class, DiscountTypePolicy::class);
        Gate::policy(FeeHead::class, FeeHeadPolicy::class);
        Gate::policy(FeeInstallment::class, FeeInstallmentPolicy::class);
        Gate::policy(FeeInvoice::class, FeeInvoicePolicy::class);
        Gate::policy(FineRule::class, FineRulePolicy::class);
        Gate::policy(LedgerAccount::class, LedgerAccountPolicy::class);
        Gate::policy(LedgerEntry::class, LedgerEntryPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Refund::class, RefundPolicy::class);
        Gate::policy(FeeStructure::class, FeeStructurePolicy::class);
        Gate::policy(StudentDiscount::class, StudentDiscountPolicy::class);
        Gate::policy(StudentFeeAssignment::class, StudentFeeAssignmentPolicy::class);
        Gate::policy(Designation::class, DesignationPolicy::class);
        Gate::policy(Staff::class, StaffPolicy::class);
        Gate::policy(Guardian::class, GuardianPolicy::class);
        Gate::policy(StudentCategory::class, StudentCategoryPolicy::class);
        Gate::policy(StudentDocument::class, StudentDocumentPolicy::class);
        Gate::policy(StudentHouse::class, StudentHousePolicy::class);
        Gate::policy(StudentMedicalRecord::class, StudentMedicalRecordPolicy::class);
        Gate::policy(StudentNote::class, StudentNotePolicy::class);
        Gate::policy(AcademicYear::class, AcademicManagementPolicy::class);
        Gate::policy(AcademicTerm::class, AcademicManagementPolicy::class);
        Gate::policy(SchoolClass::class, AcademicManagementPolicy::class);
        Gate::policy(Section::class, AcademicManagementPolicy::class);
        Gate::policy(Subject::class, AcademicManagementPolicy::class);
        Gate::policy(ClassSubjectAssignment::class, AcademicManagementPolicy::class);
        Gate::policy(TeacherAssignment::class, AcademicManagementPolicy::class);
        Gate::policy(Curriculum::class, AcademicManagementPolicy::class);
        Gate::policy(LessonPlan::class, AcademicManagementPolicy::class);
        Gate::policy(HomeworkAssignment::class, AcademicManagementPolicy::class);
        Gate::policy(AcademicCalendarEvent::class, AcademicManagementPolicy::class);
        Gate::policy(GradingStructure::class, AcademicManagementPolicy::class);
    }
}
