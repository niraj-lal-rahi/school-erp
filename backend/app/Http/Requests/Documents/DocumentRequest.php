<?php

namespace App\Http\Requests\Documents;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

abstract class DocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('school_id')) {
            $schoolId = $this->user()?->school_id ?? app(\App\Support\TenantContext::class)->id();

            if ($schoolId) {
                $this->merge(['school_id' => $schoolId]);
            }
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

    protected function uniqueForSchool(string $table, string $column, ?int $schoolId, ?int $ignoreId = null): Unique
    {
        return Rule::unique($table, $column)
            ->where(fn ($query) => $query->where('school_id', $schoolId))
            ->ignore($ignoreId);
    }

    protected function existsForSchool(string $table, string $column = 'id', ?int $schoolId = null): Exists
    {
        return Rule::exists($table, $column)
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function userExists(?int $schoolId = null): Exists
    {
        return Rule::exists('users', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function roleExists(?int $schoolId = null): Exists
    {
        return Rule::exists('roles', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where(function ($roleQuery) use ($schoolId): void {
                        $roleQuery->whereNull('school_id')->orWhere('school_id', $schoolId);
                    });
                }
            });
    }

    protected function studentExists(?int $schoolId = null): Exists
    {
        return Rule::exists('students', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function staffExists(?int $schoolId = null): Exists
    {
        return Rule::exists('staff', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function guardianExists(?int $schoolId = null): Exists
    {
        return Rule::exists('guardians', 'id')
            ->where(function ($query) use ($schoolId): void {
                if ($schoolId !== null) {
                    $query->where('school_id', $schoolId);
                }
            });
    }

    protected function ownerRule(?int $schoolId = null): array
    {
        return [
            'required',
            'string',
            Rule::in(['student', 'staff', 'guardian', 'tenant', 'user', 'general']),
        ];
    }

    protected function fileRules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'max:20480',
            'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt',
        ];
    }

    protected function permissionRules(?int $schoolId = null): array
    {
        return [
            'nullable',
            'array',
        ];
    }
}
