<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentCategory extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'document_categories';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'applies_to',
        'requires_verification',
        'has_expiry',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requires_verification' => 'boolean',
            'has_expiry' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }
}
