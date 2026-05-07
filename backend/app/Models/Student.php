<?php

namespace App\Models;

use App\Modules\Tenant\Casts\EncryptedStringCast;
use App\Models\Concerns\BelongsToSchool;
use App\Models\Concerns\OptimizesQueryFilters;
use App\Models\Finance\FeeInvoice;
use App\Models\Finance\Payment;
use App\Models\Finance\Receipt;
use App\Models\Finance\StudentFeeAssignment;
use App\Models\Attendance\StudentAttendanceRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Transport\StudentTransportAllocation;
use App\Models\Transport\TransportTripLog;

class Student extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use OptimizesQueryFilters;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'user_id',
        'uuid',
        'admission_no',
        'roll_no',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'preferred_name',
        'email',
        'phone',
        'aadhaar_no',
        'national_id',
        'religion',
        'gender',
        'date_of_birth',
        'admission_date',
        'joining_date',
        'blood_group',
        'status',
        'current_status',
        'photo_path',
        'address',
        'medical_notes',
        'notes',
        'category_id',
        'house_id',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'school_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
            'joining_date' => 'date',
            'address' => 'array',
            'aadhaar_no' => EncryptedStringCast::class,
            'medical_notes' => EncryptedStringCast::class,
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim((string) ($this->attributes['full_name'] ?? ($this->first_name.' '.$this->middle_name.' '.$this->last_name)));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot([
                'school_id',
                'relationship',
                'relationship_label',
                'is_primary',
                'is_emergency_contact',
                'pickup_authorized',
                'financial_responsibility_percentage',
                'notes',
            ])
            ->withTimestamps();
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function feeAssignments(): HasMany
    {
        return $this->hasMany(StudentFeeAssignment::class);
    }

    public function feeInvoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Receipt::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StudentAttendanceRecord::class);
    }

    public function transportAllocations(): HasMany
    {
        return $this->hasMany(StudentTransportAllocation::class, 'student_id');
    }

    public function transportTripLogs(): HasMany
    {
        return $this->hasMany(TransportTripLog::class, 'student_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(StudentMedicalRecord::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(StudentStatusHistory::class);
    }

    public function notesEntries(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function latestMedicalRecord(): HasOne
    {
        return $this->hasOne(StudentMedicalRecord::class)->latestOfMany();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StudentCategory::class, 'category_id');
    }

    public function house(): BelongsTo
    {
        return $this->belongsTo(StudentHouse::class, 'house_id');
    }
}
