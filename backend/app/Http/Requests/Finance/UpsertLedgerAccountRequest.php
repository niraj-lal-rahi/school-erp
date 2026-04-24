<?php

namespace App\Http\Requests\Finance;

use App\Enums\Finance\LedgerAccountType;
use App\Models\Finance\LedgerAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertLedgerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ledgerAccount = $this->route('ledgerAccount');

        if ($ledgerAccount instanceof LedgerAccount) {
            return $this->user()?->can('update', $ledgerAccount) ?? false;
        }

        return $this->user()?->can('create', LedgerAccount::class) ?? false;
    }

    public function rules(): array
    {
        $ledgerAccount = $this->route('ledgerAccount');
        $schoolId = $this->user()?->school_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('finance_ledger_accounts', 'code')
                    ->where(fn ($query) => $query->where('school_id', $schoolId))
                    ->ignore($ledgerAccount?->id),
            ],
            'account_type' => ['required', Rule::in(LedgerAccountType::values())],
            'parent_id' => ['nullable', 'integer', Rule::exists('finance_ledger_accounts', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
