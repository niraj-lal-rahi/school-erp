<?php

namespace App\Models\Communication;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunicationChannel extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'communication_channels';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'channel_type',
        'provider',
        'configuration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }
}
