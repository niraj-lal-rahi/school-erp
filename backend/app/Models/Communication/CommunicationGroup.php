<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationGroup extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'communication_groups';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'group_type',
        'class_id',
        'section_id',
        'description',
        'status',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunicationGroupMember::class, 'group_id');
    }
}
