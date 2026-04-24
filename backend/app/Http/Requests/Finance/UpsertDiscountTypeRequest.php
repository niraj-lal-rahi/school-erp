<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertDiscountTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $discountType = $this->route('discountType');

        if ($discountType instanceof DiscountType) {
            return $this->user()?->can('update', $discountType) ?? false;
        }

        return $this->user()?->can('create', DiscountType::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;
        $discountType = $this->route('discountType');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_discount_types', 'code')
                    ->ignore($discountType?->id)
                    ->where(fn ($query) => $query->where('school_id', $schoolId)),
            ],
            'discount_type' => ['required', Rule::in(['fixed', 'percentage'])],
            'value' => ['required', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
        ];
    }
}
