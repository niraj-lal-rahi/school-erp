<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Concerns\OptimizesQueryFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use OptimizesQueryFilters;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'uuid',
        'first_name',
        'middle_name',
        'last_name',
        'full_name',
        'relationship_type',
        'email',
        'phone',
        'alternate_phone',
        'occupation',
        'annual_income',
        'education',
        'aadhaar_no',
        'national_id',
        'photo_path',
        'is_primary',
        'can_receive_sms',
        'can_receive_email',
        'can_pickup_student',
        'address',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'country',
        'postal_code',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'annual_income' => 'decimal:2',
            'is_primary' => 'bool',
            'can_receive_sms' => 'bool',
            'can_receive_email' => 'bool',
            'can_pickup_student' => 'bool',
        ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
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
}
