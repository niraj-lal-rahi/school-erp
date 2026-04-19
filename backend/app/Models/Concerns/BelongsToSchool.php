<?php

namespace App\Models\Concerns;

use App\Models\School;
use App\Support\Multitenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::creating(function ($model): void {
            if ($model->school_id || ! app(TenantContext::class)->hasTenant()) {
                return;
            }

            $model->school_id = app(TenantContext::class)->id();
        });

        static::addGlobalScope('school', function (Builder $builder): void {
            $context = app(TenantContext::class);

            if (! $context->hasTenant()) {
                return;
            }

            $builder->where($builder->getModel()->getTable().'.school_id', $context->id());
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
