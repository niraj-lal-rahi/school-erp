<?php

namespace App\Http\Requests\Reports;

use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class ReportsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function tenantId(): ?int
    {
        return app(TenantContext::class)->id() ?? $this->user()?->school_id;
    }

    protected function existsInTenant(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function uniqueInTenant(string $table, string $column, ?int $ignoreId = null): Unique
    {
        return Rule::unique($table, $column)
            ->where(fn ($query) => $query->where('school_id', $this->tenantId()))
            ->ignore($ignoreId);
    }

    protected function routeModelId(string $key): ?int
    {
        $value = $this->route($key);

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return is_numeric($value) ? (int) $value : null;
    }
}
