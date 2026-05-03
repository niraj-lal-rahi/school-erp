<?php

namespace App\Models\Documents;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentBulkUpload extends Model
{
    use BelongsToSchool;

    protected $table = 'document_bulk_uploads';

    protected $fillable = [
        'school_id',
        'upload_type',
        'file_path',
        'status',
        'total_files',
        'success_count',
        'failed_count',
        'error_log',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'total_files' => 'integer',
            'success_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
