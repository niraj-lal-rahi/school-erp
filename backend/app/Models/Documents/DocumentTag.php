<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTag extends Model
{
    use BelongsToSchool;

    protected $table = 'document_tags';

    protected $fillable = [
        'school_id',
        'name',
        'code',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'document_tag_mappings', 'tag_id', 'document_id')
            ->using(DocumentTagMapping::class)
            ->withPivot(['id', 'school_id'])
            ->withTimestamps();
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(DocumentTagMapping::class, 'tag_id');
    }
}
