<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait TenantScoped
{
    use BelongsToSchool;

    public function scopeForTenant(Builder $query, int $schoolId): Builder
    {
        return $query->withoutGlobalScope('school')
            ->where($query->getModel()->getTable().'.school_id', $schoolId);
    }

    public function belongsToTenant(int $schoolId): bool
    {
        return (int) ($this->school_id ?? 0) === $schoolId;
    }
}
