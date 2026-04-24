<?php

namespace App\Models\HR;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryComponent extends Model
{
    use BelongsToSchool;
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'component_type',
        'calculation_type',
        'default_value',
        'taxable',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'default_value' => 'decimal:2',
            'taxable' => 'bool',
        ];
    }

    public function structureItems(): HasMany
    {
        return $this->hasMany(SalaryStructureItem::class);
    }
}
