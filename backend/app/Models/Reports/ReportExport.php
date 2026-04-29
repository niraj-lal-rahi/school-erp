<?php

namespace App\Models\Reports;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    use BelongsToSchool;

    protected $table = 'report_exports';

    protected $fillable = [
        'school_id',
        'report_run_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'downloaded_count',
        'last_downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'last_downloaded_at' => 'datetime',
        ];
    }

    public function reportRun(): BelongsTo
    {
        return $this->belongsTo(ReportRun::class, 'report_run_id');
    }
}
