<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\FeeDueFrequency;
use App\Models\Finance\FeeStructure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFeeStructureRequest extends FormRequest
{
    public function authorize(): bool
    {
        $feeStructure = $this->route('feeStructure');

        if ($feeStructure instanceof FeeStructure) {
            return $this->user()?->can('update', $feeStructure) ?? false;
        }

        return $this->user()?->can('create', FeeStructure::class) ?? false;
    }

    public function rules(): array
    {
        $feeStructure = $this->route('feeStructure');
        $schoolId = $this->user()?->school_id;

        return [
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'school_class_id' => ['nullable', 'integer', Rule::exists('school_classes', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_fee_structures', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($feeStructure?->id),
            ],
            'description' => ['nullable', 'string'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.fee_head_id' => ['required', 'integer', Rule::exists('finance_fee_heads', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
            'items.*.due_frequency' => ['required', 'string', Rule::in(FeeDueFrequency::values())],
            'items.*.due_day' => ['nullable', 'integer', 'between:1,31'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
