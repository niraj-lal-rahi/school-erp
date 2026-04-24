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
        $this->app->bind(DepartmentRepositoryInterface::class, DepartmentRepository::class);
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
        Gate::policy(Department::class, DepartmentPolicy::class);
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
