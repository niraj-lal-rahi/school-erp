<?php

namespace App\Models\Communication;

use App\Models\AcademicYear;
use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'announcements';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'title',
        'content',
        'announcement_type',
        'audience_type',
        'class_id',
        'section_id',
        'publish_at',
        'expires_at',
        'priority',
        'status',
        'created_by',
        'published_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
            'expires_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(AnnouncementRecipient::class, 'announcement_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class, 'announcement_id');
    }
}
