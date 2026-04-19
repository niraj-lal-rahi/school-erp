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
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Policies\AcademicManagement\AcademicManagementPolicy;
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
use App\Repositories\Contracts\SchoolRepositoryInterface;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\SchoolRepository;
use App\Repositories\Eloquent\StudentRepository;
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
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
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
