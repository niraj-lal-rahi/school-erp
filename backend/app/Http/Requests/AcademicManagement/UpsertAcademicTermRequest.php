<?php

namespace App\Http\Requests\AcademicManagement;

use App\Enums\AcademicManagement\AcademicStatus;
use App\Models\AcademicManagement\AcademicTerm;
use App\Models\AcademicYear;
use Illuminate\Validation\Rule;

class UpsertAcademicTermRequest extends AcademicManagementRequest
{
    public function rules(): array
    {
        /** @var AcademicTerm|null $term */
        $term = $this->route('term');

        return [
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('academic_terms', 'code')
                    ->where('school_id', $this->tenantId())
                    ->where('academic_year_id', $this->integer('academic_year_id'))
                    ->ignore($term?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'sequence' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::enum(AcademicStatus::class)],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $academicYear = AcademicYear::query()->find($this->integer('academic_year_id'));
                if (! $academicYear) {
                    return;
                }

                if ($this->date('start_date')->lt($academicYear->start_date) || $this->date('end_date')->gt($academicYear->end_date)) {
                    $validator->errors()->add('start_date', 'Term dates must fall within the selected academic year.');
                }
            },
        ];
    }
}
