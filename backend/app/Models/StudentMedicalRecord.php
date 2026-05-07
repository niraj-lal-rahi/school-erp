<?php

namespace App\Models;

use App\Modules\Tenant\Casts\EncryptedArrayCast;
use App\Modules\Tenant\Casts\EncryptedPhoneCast;
use App\Modules\Tenant\Casts\EncryptedStringCast;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentMedicalRecord extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'blood_group',
        'height',
        'weight',
        'allergies',
        'medical_conditions',
        'medications',
        'doctor_name',
        'doctor_phone',
        'hospital_name',
        'emergency_contact_name',
        'emergency_contact_phone',
        'insurance_provider',
        'insurance_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'height' => 'decimal:2',
            'weight' => 'decimal:2',
            'allergies' => EncryptedArrayCast::class,
            'medical_conditions' => EncryptedArrayCast::class,
            'medications' => EncryptedArrayCast::class,
            'doctor_phone' => EncryptedPhoneCast::class,
            'emergency_contact_phone' => EncryptedPhoneCast::class,
            'insurance_number' => EncryptedStringCast::class,
            'notes' => EncryptedStringCast::class,
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
