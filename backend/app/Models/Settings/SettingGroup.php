<?php

namespace App\Models\Settings;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingGroup extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $table = 'setting_groups';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'group_id');
    }
}
