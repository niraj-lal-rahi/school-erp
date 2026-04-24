<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\FeeAmountType;
use App\Models\Finance\FeeHead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertFeeHeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        $feeHead = $this->route('feeHead');

        if ($feeHead instanceof FeeHead) {
            return $this->user()?->can('update', $feeHead) ?? false;
        }

        return $this->user()?->can('create', FeeHead::class) ?? false;
    }

    public function rules(): array
    {
        $feeHead = $this->route('feeHead');
        $schoolId = $this->user()?->school_id;

        return [
            'fee_category_id' => [
                'required',
                'integer',
                Rule::exists('finance_fee_categories', 'id')->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_fee_heads', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($feeHead?->id),
            ],
            'amount_type' => ['required', 'string', Rule::in(FeeAmountType::values())],
            'default_amount' => ['nullable', 'numeric', 'min:0'],
            'is_refundable' => ['sometimes', 'boolean'],
            'is_optional' => ['sometimes', 'boolean'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
