<?php

namespace App\Http\Requests\HR;

use App\Models\HR\Staff;
use App\Models\HR\StaffBankDetail;
use Illuminate\Foundation\Http\FormRequest;

class UpsertStaffBankDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        $staff = $this->route('staff');
        $bankDetail = $this->route('staffBankDetail');

        if ($staff instanceof Staff) {
            return $this->user()?->can('update', $staff) ?? false;
        }

        if ($bankDetail instanceof StaffBankDetail) {
            return $this->user()?->can('update', $bankDetail->staff) ?? false;
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:50'],
            'branch_name' => ['nullable', 'string', 'max:255'],
            'account_type' => ['nullable', 'string', 'max:100'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
