<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use App\Models\Concerns\OptimizesQueryFilters;
use App\Models\Communication\MessageTemplate;
use App\Models\Guardian;
use App\Models\HR\Staff;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToSchool;
    use OptimizesQueryFilters;
    use SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'school_id',
        'category_id',
        'folder_id',
        'owner_type',
        'owner_id',
        'title',
        'description',
        'document_no',
        'issue_date',
        'expiry_date',
        'verification_status',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'folder_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DocumentFile::class, 'document_id')->orderByDesc('version_no');
    }

    public function currentFile(): HasOne
    {
        return $this->hasOne(DocumentFile::class, 'document_id')->where('is_current', true)->latestOfMany('version_no');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(DocumentPermission::class, 'document_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(DocumentVerification::class, 'document_id')->latest('id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(DocumentTag::class, 'document_tag_mappings', 'document_id', 'tag_id')
            ->using(DocumentTagMapping::class)
            ->withPivot(['id', 'school_id'])
            ->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(DocumentAuditLog::class, 'document_id')->latest('id');
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function ownerStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'owner_id');
    }

    public function ownerStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'owner_id');
    }

    public function ownerGuardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'owner_id');
    }

    public function ownerTenant(): BelongsTo
    {
        return $this->belongsTo(School::class, 'owner_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }
}
