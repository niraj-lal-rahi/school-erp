<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentDocument extends Model
{
    use BelongsToSchool;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'student_id',
        'uploaded_by',
        'document_type',
        'title',
        'disk',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'issued_by',
        'issued_date',
        'expiry_date',
        'verification_status',
        'remarks',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'issued_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
