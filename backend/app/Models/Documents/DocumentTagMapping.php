<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DocumentTagMapping extends Pivot
{
    use BelongsToSchool;

    protected $table = 'document_tag_mappings';

    protected $fillable = [
        'school_id',
        'document_id',
        'tag_id',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(DocumentTag::class, 'tag_id');
    }
}
