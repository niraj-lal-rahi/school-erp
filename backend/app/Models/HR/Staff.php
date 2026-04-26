<?php

namespace App\Models\HR;

use App\Models\AcademicManagement\HomeworkAssignment;
use App\Models\AcademicManagement\LessonPlan;
use App\Models\AcademicManagement\TeacherAssignment;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Timetable\TimetableEntry;
use App\Models\Timetable\TimetableSubstitution;
use App\Models\Transport\StaffTransportAllocation;
use App\Models\Transport\TransportDriver;
use App\Models\Transport\TransportTripLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'school_id',
        'user_id',
        'department_id',
        'designation_id',
        'employee_code',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'alternate_phone',
        'photo_path',
        'staff_type',
        'employment_type',
        'joining_date',
        'leaving_date',
        'current_status',
        'qualification_summary',
        'experience_years',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'leaving_date' => 'date',
            'experience_years' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'staff_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StaffDocument::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(StaffEmergencyContact::class);
    }

    public function qualifications(): HasMany
    {
        return $this->hasMany(StaffQualification::class);
    }

    public function workExperiences(): HasMany
    {
        return $this->hasMany(StaffWorkExperience::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(StaffLeaveApplication::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(StaffPayslip::class);
    }

    public function bankDetails(): HasMany
    {
        return $this->hasMany(StaffBankDetail::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StaffStatusHistory::class);
    }

    public function notesEntries(): HasMany
    {
        return $this->hasMany(StaffNote::class);
    }

    public function lessonPlans(): HasMany
    {
        return $this->hasMany(LessonPlan::class, 'staff_id');
    }

    public function homeworkAssignments(): HasMany
    {
        return $this->hasMany(HomeworkAssignment::class, 'staff_id');
    }

    public function timetableEntries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'staff_id');
    }

    public function originalSubstitutions(): HasMany
    {
        return $this->hasMany(TimetableSubstitution::class, 'original_staff_id');
    }

    public function substituteAssignments(): HasMany
    {
        return $this->hasMany(TimetableSubstitution::class, 'substitute_staff_id');
    }

    public function transportDriverProfiles(): HasMany
    {
        return $this->hasMany(TransportDriver::class, 'staff_id');
    }

    public function transportAllocations(): HasMany
    {
        return $this->hasMany(StaffTransportAllocation::class, 'staff_id');
    }

    public function transportTripLogs(): HasMany
    {
        return $this->hasMany(TransportTripLog::class, 'staff_id');
    }
}
