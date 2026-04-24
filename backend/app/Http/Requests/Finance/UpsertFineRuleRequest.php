<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\FineRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFineRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $fineRule = $this->route('fineRule');

        if ($fineRule instanceof FineRule) {
            return $this->user()?->can('update', $fineRule) ?? false;
        }

        return $this->user()?->can('create', FineRule::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;
        $fineRule = $this->route('fineRule');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_fine_rules', 'code')
                    ->ignore($fineRule?->id)
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'fee_head_id' => ['nullable', 'integer', Rule::exists('finance_fee_heads', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'fine_type' => ['required', Rule::in(['fixed', 'daily', 'percentage'])],
            'amount' => ['required', 'numeric', 'min:0'],
            'grace_days' => ['nullable', 'integer', 'min:0'],
            'max_fine_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'max:50'],
        ];
    }
}
