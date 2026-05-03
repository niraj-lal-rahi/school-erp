<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentFile extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'document_files';

    protected $fillable = [
        'school_id',
        'document_id',
        'version_no',
        'file_name',
        'original_file_name',
        'file_path',
        'disk',
        'mime_type',
        'file_size',
        'checksum',
        'uploaded_by',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_current' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
