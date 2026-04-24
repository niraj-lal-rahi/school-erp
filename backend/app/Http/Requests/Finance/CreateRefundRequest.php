<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\Refund;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRefundRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Refund::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'payment_id' => ['required', 'integer', Rule::exists('finance_payments', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'refund_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string'],
        ];
    }
}
