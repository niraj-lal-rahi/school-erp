<?php

namespace App\Models\Examination;

use App\Models\Concerns\BelongsToSchool;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultPublication extends Model
{
    use BelongsToSchool;

    protected $table = 'result_publications';

    protected $fillable = [
        'school_id',
        'exam_id',
        'published_by',
        'published_at',
        'is_public',
        'notify_users',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_public' => 'bool',
            'notify_users' => 'bool',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
