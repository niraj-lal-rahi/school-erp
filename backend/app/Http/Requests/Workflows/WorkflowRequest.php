<?php

namespace App\Http\Requests\Workflows;

use App\Support\Multitenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class WorkflowRequest extends FormRequest
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
        if (! $this->has('school_id') && $this->tenantId()) {
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

    protected function roleExists(): Exists
    {
        return Rule::exists('roles', 'id')->where(function ($query): void {
            $query->where(function ($roleQuery): void {
                $roleQuery->whereNull('school_id')
                    ->orWhere('school_id', $this->tenantId());
            });
        });
    }

    protected function userExists(): Exists
    {
        return Rule::exists('users', 'id')->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function templateExists(): Exists
    {
        return Rule::exists('message_templates', 'id')->where(
            fn ($query) => $query->where('school_id', $this->tenantId())
        );
    }

    protected function moduleOptionsForWorkflow(): array
    {
        return ['admissions', 'fees', 'attendance', 'hr', 'exams', 'communication', 'transport', 'general'];
    }

    protected function moduleOptionsForAutomation(): array
    {
        return ['fees', 'attendance', 'exams', 'communication', 'transport', 'hr', 'general'];
    }

    protected function moduleOptionsForReminder(): array
    {
        return ['fees', 'attendance', 'exams', 'transport', 'hr', 'general'];
    }
}
