<?php

namespace App\Http\Requests\Settings;

use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function tenantId(): ?int
    {
        return app(TenantContext::class)->id() ?? $this->user()?->school_id;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('school_id') && $this->tenantId()) {
            $this->merge(['school_id' => $this->tenantId()]);
        }
    }

    protected function routeModelId(string $key): ?int
    {
        $value = $this->route($key);

        if (is_object($value) && isset($value->id)) {
            return (int) $value->id;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    protected function uniqueInTenant(string $table, string $column, ?int $ignoreId = null): Unique
    {
        return Rule::unique($table, $column)
            ->where(fn ($query) => $query->where('school_id', $this->tenantId()))
            ->ignore($ignoreId);
    }

    protected function globalOrTenantUnique(string $table, string $column, string $scopeColumn, ?string $scope = null, ?int $ignoreId = null): Unique
    {
        return Rule::unique($table, $column)
            ->where(function ($query) use ($scopeColumn, $scope): void {
                $query->where($scopeColumn, $scope ?? $this->input('scope'))
                    ->where(function ($tenantQuery): void {
                        if (($this->input('scope') ?? null) === 'global') {
                            $tenantQuery->whereNull('school_id');
                        } else {
                            $tenantQuery->where('school_id', $this->tenantId());
                        }
                    });
            })
            ->ignore($ignoreId);
    }

    protected function existsInTenant(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function existsInGlobalOrTenant(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)->where(function ($query): void {
            $query->whereNull('school_id')
                ->orWhere('school_id', $this->tenantId());
        });
    }

    protected function settingValueTypes(): array
    {
        return ['string', 'integer', 'boolean', 'json', 'encrypted', 'file'];
    }

    protected function settingScopes(): array
    {
        return ['global', 'tenant'];
    }
}
