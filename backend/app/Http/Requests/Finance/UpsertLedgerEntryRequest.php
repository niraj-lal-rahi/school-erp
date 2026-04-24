<?php

namespace App\Http\Requests\Finance;

use App\Models\Finance\LedgerEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertLedgerEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ledgerEntry = $this->route('ledgerEntry');

        if ($ledgerEntry instanceof LedgerEntry) {
            return $this->user()?->can('update', $ledgerEntry) ?? false;
        }

        return $this->user()?->can('create', LedgerEntry::class) ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->user()?->school_id;

        return [
            'ledger_account_id' => ['required', 'integer', Rule::exists('finance_ledger_accounts', 'id')->where(fn ($query) => $query->where('school_id', $schoolId))],
            'source_type' => ['required', 'string', 'max:255'],
            'source_id' => ['required', 'integer', 'min:1'],
            'entry_date' => ['required', 'date'],
            'debit' => ['nullable', 'numeric', 'min:0'],
            'credit' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
