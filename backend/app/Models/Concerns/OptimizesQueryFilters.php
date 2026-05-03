<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait OptimizesQueryFilters
{
    public function scopeForTenant(Builder $query, ?int $schoolId): Builder
    {
        if ($schoolId === null) {
            return $query;
        }

        return $query->where($query->getModel()->getTable().'.school_id', $schoolId);
    }

    public function scopeWhereStatus(Builder $query, ?string $status, string $column = 'status'): Builder
    {
        if ($status === null || $status === '') {
            return $query;
        }

        return $query->where($query->getModel()->getTable().'.'.$column, $status);
    }

    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to, string $column = 'created_at'): Builder
    {
        return $query
            ->when($from, fn (Builder $builder, string $value) => $builder->whereDate($column, '>=', $value))
            ->when($to, fn (Builder $builder, string $value) => $builder->whereDate($column, '<=', $value));
    }

    public function scopeSearchAcross(Builder $query, ?string $term, array $columns): Builder
    {
        if ($term === null || trim($term) === '' || $columns === []) {
            return $query;
        }

        return $query->where(function (Builder $builder) use ($term, $columns): void {
            foreach ($columns as $index => $column) {
                if ($index === 0) {
                    $builder->where($column, 'like', "%{$term}%");

                    continue;
                }

                $builder->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
