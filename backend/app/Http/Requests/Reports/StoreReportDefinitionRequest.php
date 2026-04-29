<?php

namespace App\Http\Requests\Reports;

use Closure;
use Illuminate\Validation\Rule;

class StoreReportDefinitionRequest extends ReportsRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:80', $this->uniqueInTenant('report_definitions', 'code')],
            'module' => ['required', 'string', Rule::in(['dashboard', 'attendance', 'finance', 'exams', 'transport', 'communication', 'custom'])],
            'description' => ['nullable', 'string'],
            'query_config' => ['required', 'array', $this->validQueryConfig()],
            'query_config.filters' => ['nullable', 'array'],
            'query_config.fields' => ['nullable', 'array'],
            'query_config.group_by' => ['nullable', 'array'],
            'query_config.metrics' => ['nullable', 'array'],
            'default_filters' => ['nullable', 'array'],
            'is_system' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function validQueryConfig(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_array($value)) {
                $fail('The query config must be a valid object payload.');
                return;
            }

            $allowed = ['filters', 'fields', 'group_by', 'metrics', 'sort', 'limit', 'chart_type'];
            $invalidKeys = array_diff(array_keys($value), $allowed);

            if ($invalidKeys !== []) {
                $fail('The query config contains unsupported keys: '.implode(', ', $invalidKeys).'.');
            }
        };
    }
}
