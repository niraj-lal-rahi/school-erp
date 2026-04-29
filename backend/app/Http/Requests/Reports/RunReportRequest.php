<?php

namespace App\Http\Requests\Reports;

use Closure;
use Illuminate\Validation\Rule;

class RunReportRequest extends ReportsRequest
{
    public function rules(): array
    {
        return [
            'report_definition_id' => ['required', 'integer', $this->existsInTenant('report_definitions')],
            'run_type' => ['sometimes', 'string', Rule::in(['manual', 'scheduled'])],
            'file_type' => ['required', 'string', Rule::in(['csv', 'xlsx', 'pdf', 'json'])],
            'parameters' => ['nullable', 'array', $this->validParameters()],
            'parameters.filters' => ['nullable', 'array'],
            'parameters.fields' => ['nullable', 'array'],
            'parameters.group_by' => ['nullable', 'array'],
            'parameters.metrics' => ['nullable', 'array'],
            'parameters.date_from' => ['nullable', 'date'],
            'parameters.date_to' => ['nullable', 'date', 'after_or_equal:parameters.date_from'],
        ];
    }

    protected function validParameters(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value !== null && ! is_array($value)) {
                $fail('The parameters field must be a valid object payload.');
            }
        };
    }
}
