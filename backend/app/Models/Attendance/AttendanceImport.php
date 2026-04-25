<?php

namespace App\Models\Attendance;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceImport extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_imports';

    protected $fillable = [
        'school_id',
        'file_path',
        'import_type',
        'import_date',
        'status',
        'total_records',
        'success_count',
        'failed_count',
        'logs',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'import_date' => 'date',
            'logs' => 'array',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
